<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature\Commands;

use Hyde\Facades\Filesystem;
use Hyde\Hyde;
use Hyde\Pages\InMemoryPage;
use Hyde\Pages\DocumentationPage;
use Hyde\Testing\TestCase;
use Hyde\Framework\Features\Documentation\DocumentationSearchIndex;
use Hyde\Framework\Features\Documentation\Versioning\DocumentationVersions;

#[\PHPUnit\Framework\Attributes\CoversClass(\Hyde\Console\Commands\BuildSearchCommand::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(\Hyde\Framework\Features\Documentation\DocumentationSearchPage::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(\Hyde\Framework\Features\Documentation\DocumentationSearchIndex::class)]
class BuildSearchCommandTest extends TestCase
{
    public function testItCreatesTheSearchJsonFile()
    {
        $this->assertFileDoesNotExist(Hyde::path('_site/docs/search.json'));

        $this->artisan('build:search')->assertExitCode(0);
        $this->assertFileExists(Hyde::path('_site/docs/search.json'));

        Filesystem::unlink('_site/docs/search.json');
        Filesystem::unlink('_site/docs/search.html');
    }

    public function testItCreatesTheSearchPage()
    {
        $this->assertFileDoesNotExist(Hyde::path('_site/docs/search.html'));

        $this->artisan('build:search')->assertExitCode(0);
        $this->assertFileExists(Hyde::path('_site/docs/search.html'));

        Filesystem::unlink('_site/docs/search.json');
        Filesystem::unlink('_site/docs/search.html');
    }

    public function testItDoesNotCreateTheSearchPageIfDisabled()
    {
        config(['docs.create_search_page' => false]);

        $this->artisan('build:search')->assertExitCode(0);
        $this->assertFileDoesNotExist(Hyde::path('_site/docs/search.html'));

        Filesystem::unlink('_site/docs/search.json');
    }

    public function testItDoesNotDisplayTheEstimationMessageWhenItIsLessThan1Second()
    {
        $this->artisan('build:search')
            ->doesntExpectOutputToContain('> This will take an estimated')
            ->assertExitCode(0);

        Filesystem::unlink('_site/docs/search.json');
        Filesystem::unlink('_site/docs/search.html');
    }

    public function testSearchFilesCanBeGeneratedForCustomDocsOutputDirectory()
    {
        DocumentationPage::setOutputDirectory('foo');

        $this->artisan('build:search')->assertExitCode(0);
        $this->assertFileExists(Hyde::path('_site/foo/search.json'));
        $this->assertFileExists(Hyde::path('_site/foo/search.html'));

        Filesystem::deleteDirectory('_site/foo');
    }

    public function testSearchFilesCanBeGeneratedForCustomSiteOutputDirectory()
    {
        Hyde::setOutputDirectory('foo');

        $this->artisan('build:search')->assertExitCode(0);
        $this->assertFileExists(Hyde::path('foo/docs/search.json'));
        $this->assertFileExists(Hyde::path('foo/docs/search.html'));

        Filesystem::deleteDirectory('foo');
    }

    public function testSearchFilesCanBeGeneratedForCustomSiteAndDocsOutputDirectories()
    {
        Hyde::setOutputDirectory('foo');
        DocumentationPage::setOutputDirectory('bar');

        $this->artisan('build:search')->assertExitCode(0);
        $this->assertFileExists(Hyde::path('foo/bar/search.json'));
        $this->assertFileExists(Hyde::path('foo/bar/search.html'));

        Filesystem::deleteDirectory('foo');
    }

    public function testSearchFilesCanBeGeneratedForCustomSiteAndNestedDocsOutputDirectories()
    {
        Hyde::setOutputDirectory('foo/bar');
        DocumentationPage::setOutputDirectory('baz');

        $this->artisan('build:search')->assertExitCode(0);
        $this->assertFileExists(Hyde::path('foo/bar/baz/search.json'));
        $this->assertFileExists(Hyde::path('foo/bar/baz/search.html'));

        Filesystem::deleteDirectory('foo');
    }

    public function testVersionedSearchFilesAreGeneratedForEachDocumentationVersion()
    {
        config(['docs.versions' => ['1.x', '2.x']]);

        $this->artisan('build:search')->assertExitCode(0);

        $this->assertFileExists(Hyde::path('_site/docs/1.x/search.json'));
        $this->assertFileExists(Hyde::path('_site/docs/2.x/search.json'));
        $this->assertFileExists(Hyde::path('_site/docs/1.x/search.html'));
        $this->assertFileExists(Hyde::path('_site/docs/2.x/search.html'));
        $this->assertFileDoesNotExist(Hyde::path('_site/docs/search.json'));
        $this->assertFileDoesNotExist(Hyde::path('_site/docs/search.html'));

        Filesystem::deleteDirectory('_site/docs/1.x');
        Filesystem::deleteDirectory('_site/docs/2.x');
    }

    public function testVersionedSearchCommandDoesNotCreateSearchPagesWhenDisabled()
    {
        config(['docs.versions' => ['1.x', '2.x'], 'docs.create_search_page' => false]);

        $this->artisan('build:search')->assertExitCode(0);

        $this->assertFileExists(Hyde::path('_site/docs/1.x/search.json'));
        $this->assertFileExists(Hyde::path('_site/docs/2.x/search.json'));
        $this->assertFileDoesNotExist(Hyde::path('_site/docs/1.x/search.html'));
        $this->assertFileDoesNotExist(Hyde::path('_site/docs/2.x/search.html'));

        Filesystem::deleteDirectory('_site/docs/1.x');
        Filesystem::deleteDirectory('_site/docs/2.x');
    }

    public function testVersionedSearchFilesCanBeGeneratedForCustomSiteAndDocsOutputDirectories()
    {
        config(['docs.versions' => ['1.x', '2.x']]);

        Hyde::setOutputDirectory('build');
        DocumentationPage::setOutputDirectory('reference');

        try {
            $this->artisan('build:search')->assertExitCode(0);

            $this->assertFileExists(Hyde::path('build/reference/1.x/search.json'));
            $this->assertFileExists(Hyde::path('build/reference/2.x/search.json'));
            $this->assertFileExists(Hyde::path('build/reference/1.x/search.html'));
            $this->assertFileExists(Hyde::path('build/reference/2.x/search.html'));
        } finally {
            Filesystem::deleteDirectory('build');
            Hyde::setOutputDirectory('_site');
            DocumentationPage::setOutputDirectory('docs');
        }
    }

    public function testVersionedCommandUsesSearchPagesFromKernelWhenPresent()
    {
        config(['docs.versions' => ['1.x', '2.x']]);

        Hyde::pages()->addPage(new VersionedSearchIndexOverrideTestPage());

        $this->artisan('build:search')->assertExitCode(0);

        $this->assertFileExists(Hyde::path('_site/docs/1.x/search.json'));
        $this->assertFileExists(Hyde::path('_site/docs/2.x/search.json'));
        $this->assertSame('{"version":"1.x"}', Filesystem::getContents('_site/docs/1.x/search.json'));

        Filesystem::deleteDirectory('_site/docs/1.x');
        Filesystem::deleteDirectory('_site/docs/2.x');
    }

    public function test_command_uses_search_pages_from_kernel_when_present()
    {
        Hyde::pages()->addPage(new SearchIndexOverrideTestPage());

        Hyde::pages()->addPage(new InMemoryPage('docs/search', contents: fn (): string => 'Foo'));

        $this->artisan('build:search')->assertExitCode(0);

        $this->assertFileExists(Hyde::path('_site/docs/search.json'));

        $this->assertSame('{"foo":"bar"}', Filesystem::getContents('_site/docs/search.json'));

        Filesystem::unlink('_site/docs/search.json');
    }
}

class SearchIndexOverrideTestPage extends DocumentationSearchIndex
{
    public function compile(): string
    {
        return '{"foo":"bar"}';
    }

    public function getOutputPath(): string
    {
        return 'docs/search.json';
    }
}

class VersionedSearchIndexOverrideTestPage extends DocumentationSearchIndex
{
    public function __construct()
    {
        parent::__construct(DocumentationVersions::get('1.x'));
    }

    public function compile(): string
    {
        return '{"version":"1.x"}';
    }
}
