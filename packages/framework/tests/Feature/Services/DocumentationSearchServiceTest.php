<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature\Services;

use Hyde\Framework\Models\Pages\DocumentationPage;
use Hyde\Framework\Services\DocumentationSearchService as Service;
use Hyde\Hyde;
use Hyde\Testing\TestCase;

/**
 * @covers \Hyde\Framework\Services\DocumentationSearchService
 */
class DocumentationSearchServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        unlinkIfExists(Service::$filePath);
    }

    protected function tearDown(): void
    {
        unlinkIfExists(Service::$filePath);

        parent::tearDown();
    }

    public function test_it_generates_a_json_file_with_a_search_index()
    {
        $this->resetDocs();

        Hyde::touch(('_docs/foo.md'));

        $expected = [
            [
                'slug' => 'foo',
                'title' => 'Foo',
                'content' => '',
                'destination' => 'foo.html',
            ],
        ];

        Service::generate();

        $this->assertEquals(
            json_encode($expected), file_get_contents(Service::$filePath)
        );

        unlink(Hyde::path('_docs/foo.md'));
    }

    public function test_it_adds_all_files_to_search_index()
    {
        Hyde::touch(('_docs/foo.md'));
        Hyde::touch(('_docs/bar.md'));
        Hyde::touch(('_docs/baz.md'));

        $this->assertCount(3, (new Service())->run()->searchIndex);

        unlink(Hyde::path('_docs/foo.md'));
        unlink(Hyde::path('_docs/bar.md'));
        unlink(Hyde::path('_docs/baz.md'));
    }

    public function test_it_handles_generation_even_when_there_are_no_pages()
    {
        Service::generate();

        $this->assertEquals(
            '[]', file_get_contents(Service::$filePath)
        );
    }

    public function test_save_method_saves_the_file_to_the_correct_location()
    {
        Service::generate();

        $this->assertFileExists('_site/docs/search.json');
    }

    public function test_generate_page_entry_method_generates_a_page_entry()
    {
        $expected = [
            'slug' => 'foo',
            'title' => 'Bar',
            'content' => "Bar \n Hello World",
            'destination' => 'foo.html',
        ];

        file_put_contents(Hyde::path('_docs/foo.md'), "# Bar\n\n Hello World");

        $this->assertEquals(
            $expected, (new Service())->generatePageEntry(DocumentationPage::parse('foo'))
        );

        unlink(Hyde::path('_docs/foo.md'));
    }

    public function test_it_generates_a_valid_JSON()
    {
        file_put_contents(Hyde::path('_docs/foo.md'), "# Bar\n\n Hello World");
        file_put_contents(Hyde::path('_docs/bar.md'), "# Foo\n\n Hello World");

        $generatesDocumentationSearchIndexFile = (new Service())->run();
        $this->assertEquals(
            '[{"slug":"bar","title":"Foo","content":"Foo \n Hello World","destination":"bar.html"},'.
            '{"slug":"foo","title":"Bar","content":"Bar \n Hello World","destination":"foo.html"}]',
            json_encode($generatesDocumentationSearchIndexFile->searchIndex->toArray())
        );

        unlink(Hyde::path('_docs/foo.md'));
        unlink(Hyde::path('_docs/bar.md'));
    }

    public function test_get_destination_for_slug_returns_empty_string_for_index_when_pretty_url_is_enabled()
    {
        config(['site.pretty_urls' => true]);

        $this->assertEquals(
            '', (new Service())->getDestinationForSlug('index')
        );
    }

    public function test_get_destination_for_slug_returns_pretty_url_when_enabled()
    {
        config(['site.pretty_urls' => true]);

        $this->assertEquals(
            'foo', (new Service())->getDestinationForSlug('foo')
        );
    }

    public function test_excluded_pages_are_not_present_in_the_search_index()
    {
        Hyde::touch(('_docs/excluded.md'));
        config(['docs.exclude_from_search' => ['excluded']]);

        $generatesDocumentationSearchIndexFile = (new Service())->run();
        $this->assertStringNotContainsString('excluded', json_encode($generatesDocumentationSearchIndexFile->searchIndex->toArray()));

        unlink(Hyde::path('_docs/excluded.md'));
    }

    public function test_nested_source_files_do_not_retain_directory_name_in_search_index()
    {
        mkdir(Hyde::path('_docs/foo'));
        touch(Hyde::path('_docs/foo/bar.md'));

        $generatesDocumentationSearchIndexFile = (new Service())->run();
        $this->assertStringNotContainsString('foo', json_encode($generatesDocumentationSearchIndexFile->searchIndex->toArray()));

        unlink(Hyde::path('_docs/foo/bar.md'));
        rmdir(Hyde::path('_docs/foo'));
    }
}
