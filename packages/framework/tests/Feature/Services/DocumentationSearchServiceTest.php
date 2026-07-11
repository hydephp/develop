<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature\Services;

use Hyde\Framework\Actions\GeneratesDocumentationSearchIndex;
use Hyde\Framework\Features\Documentation\Versioning\DocumentationVersion;
use Hyde\Framework\Features\Documentation\Versioning\DocumentationVersions;
use Hyde\Hyde;
use Hyde\Testing\CreatesTemporaryFiles;
use Hyde\Testing\UnitTestCase;

#[\PHPUnit\Framework\Attributes\CoversClass(\Hyde\Framework\Actions\GeneratesDocumentationSearchIndex::class)]
class DocumentationSearchServiceTest extends UnitTestCase
{
    use CreatesTemporaryFiles;

    protected function setUp(): void
    {
        self::setupKernel();
        self::mockConfig();
    }

    protected function tearDown(): void
    {
        $this->cleanUpFilesystem();
    }

    public function testItGeneratesAJsonFileWithASearchIndex()
    {
        $this->file('_docs/foo.md');

        $this->assertSame(json_encode([[
            'slug' => 'foo',
            'title' => 'Foo',
            'content' => '',
            'destination' => 'foo.html',
        ]]), GeneratesDocumentationSearchIndex::handle());
    }

    public function testItAddsAllFilesToSearchIndex()
    {
        $this->file('_docs/foo.md');
        $this->file('_docs/bar.md');
        $this->file('_docs/baz.md');

        $this->assertCount(3, $this->getArray());
    }

    public function testItHandlesGenerationEvenWhenThereAreNoPages()
    {
        $this->assertSame('[]', GeneratesDocumentationSearchIndex::handle());
    }

    public function testItGeneratesAValidJson()
    {
        $this->file('_docs/foo.md', "# Bar\nHello World");
        $this->file('_docs/bar.md', "# Foo\n\nHello World");

        $this->assertSame(
            <<<'JSON'
            [{"slug":"bar","title":"Foo","content":"Foo\n\nHello World","destination":"bar.html"},{"slug":"foo","title":"Bar","content":"Bar\nHello World","destination":"foo.html"}]
            JSON,
            json_encode($this->getArray())
        );
    }

    public function testItStripsMarkdown()
    {
        $this->file('_docs/foo.md', "# Foo Bar\n**Hello** _World_");

        $this->assertSame(
            "Foo Bar\nHello World",
            $this->getArray()[0]['content']
        );
    }

    public function testGetDestinationForSlugReturnsEmptyStringForIndexWhenPrettyUrlIsEnabled()
    {
        self::mockConfig(['hyde.pretty_urls' => true]);
        $this->file('_docs/index.md');

        $this->assertSame('',
            $this->getArray()[0]['destination']
        );
    }

    public function testGetDestinationForSlugReturnsPrettyUrlWhenEnabled()
    {
        self::mockConfig(['hyde.pretty_urls' => true]);
        $this->file('_docs/foo.md');

        $this->assertSame('foo',
            $this->getArray()[0]['destination']
        );
    }

    public function testExcludedPagesAreNotPresentInTheSearchIndex()
    {
        $this->file('_docs/excluded.md');
        self::mockConfig(['docs.exclude_from_search' => ['excluded']]);

        $this->assertStringNotContainsString('excluded',
            json_encode($this->getArray())
        );
    }

    public function testPagesCanBeExcludedFromTheSearchIndexByRouteKey()
    {
        $this->file('_docs/excluded.md');
        self::mockConfig(['docs.exclude_from_search' => ['docs/excluded']]);

        $this->assertSame([], $this->getArray());
    }

    public function testVersionedPagesCanBeExcludedFromTheSearchIndexByRouteKey()
    {
        self::mockConfig([
            'docs.versions' => ['1.x', '2.x'],
            'docs.exclude_from_search' => ['docs/1.x/changelog'],
        ]);

        $this->file('_docs/1.x/changelog.md', '# Legacy Changelog');
        $this->file('_docs/2.x/changelog.md', '# Current Changelog');

        $this->assertSame([], $this->getArray(DocumentationVersions::get('1.x')));
        $this->assertSame(['Current Changelog'], array_column($this->getArray(DocumentationVersions::get('2.x')), 'title'));
    }

    public function testVersionAgnosticRouteKeyExclusionsApplyToAllVersions()
    {
        self::mockConfig([
            'docs.versions' => ['1.x', '2.x'],
            'docs.exclude_from_search' => ['docs/changelog'],
        ]);

        $this->file('_docs/1.x/changelog.md', '# Legacy Changelog');
        $this->file('_docs/2.x/changelog.md', '# Current Changelog');

        $this->assertSame([], $this->getArray(DocumentationVersions::get('1.x')));
        $this->assertSame([], $this->getArray(DocumentationVersions::get('2.x')));
    }

    public function testVersionedIndexOnlyContainsPagesForTheRequestedVersion()
    {
        self::mockConfig(['docs.versions' => ['1.x', '2.x']]);

        $this->file('_docs/shared.md', '# Shared');
        $this->file('_docs/1.x/installation.md', '# Installing 1.x');
        $this->file('_docs/2.x/installation.md', '# Installing 2.x');
        $this->file('_docs/2.x/upgrading.md', '# Upgrading');

        $this->assertSame(['Installing 1.x'], array_column($this->getArray(DocumentationVersions::get('1.x')), 'title'));
        $this->assertSame(['Installing 2.x', 'Upgrading'], array_column($this->getArray(DocumentationVersions::get('2.x')), 'title'));
    }

    public function testVersionSpecificSearchExclusionsOnlyApplyToThatVersion()
    {
        self::mockConfig([
            'docs.versions' => ['1.x', '2.x'],
            'docs.exclude_from_search' => ['1.x/changelog'],
        ]);

        $this->file('_docs/1.x/changelog.md', '# Legacy Changelog');
        $this->file('_docs/2.x/changelog.md', '# Current Changelog');

        $this->assertSame([], $this->getArray(DocumentationVersions::get('1.x')));
        $this->assertSame(['Current Changelog'], array_column($this->getArray(DocumentationVersions::get('2.x')), 'title'));
    }

    public function testVersionedIndexUsesPrettyUrlDestinations()
    {
        self::mockConfig([
            'hyde.pretty_urls' => true,
            'docs.versions' => ['1.x'],
        ]);

        $this->file('_docs/1.x/index.md', '# Home');
        $this->file('_docs/1.x/installation.md', '# Installation');

        $destinations = array_column($this->getArray(DocumentationVersions::get('1.x')), 'destination');

        $this->assertContains('', $destinations);
        $this->assertContains('installation', $destinations);
    }

    public function testNestedSourceFilesDoNotRetainDirectoryNameInSearchIndex()
    {
        $this->directory(Hyde::path('_docs/foo'));
        $this->file('_docs/foo/bar.md');

        $this->assertStringNotContainsString('foo',
            json_encode($this->getArray())
        );
    }

    public function testNumericPrefixesAreStrippedFromSearchResults()
    {
        $this->file('_docs/01-foo.md');

        $this->assertSame([[
            'slug' => 'foo',
            'title' => 'Foo',
            'content' => '',
            'destination' => 'foo.html',
        ]], $this->getArray());
    }

    public function testNumericPrefixesAreRemovedFromNestedSearchResults()
    {
        $this->directory(Hyde::path('_docs/01-category'));
        $this->file('_docs/01-category/02-item-name.md');

        $result = $this->getArray()[0];

        $this->assertSame('item-name', $result['slug']);
        $this->assertSame('item-name.html', $result['destination']);
        $this->assertStringNotContainsString('02-', json_encode($result));
        $this->assertStringNotContainsString('01-', json_encode($result));
    }

    protected function getArray(?DocumentationVersion $version = null): array
    {
        return json_decode(GeneratesDocumentationSearchIndex::handle($version), true);
    }
}
