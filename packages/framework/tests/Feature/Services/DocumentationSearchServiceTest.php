<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature\Services;

use Hyde\Facades\Filesystem;
use Hyde\Framework\Services\DocumentationSearchService;
use Hyde\Hyde;
use Hyde\Pages\DocumentationPage;
use Hyde\Testing\TestCase;

/**
 * @covers \Hyde\Framework\Services\DocumentationSearchService
 */
class DocumentationSearchServiceTest extends TestCase
{
    public function test_it_generates_a_json_file_with_a_search_index()
    {
        $this->resetDocs();

        Filesystem::touch('_docs/foo.md');

        DocumentationSearchService::generate();

        $this->assertSame(json_encode([[
            'slug' => 'foo',
            'title' => 'Foo',
            'content' => '',
            'destination' => 'foo.html',
        ]]), file_get_contents(DocumentationSearchService::$filePath));

        Filesystem::unlink('_docs/foo.md');
        Filesystem::unlink('_site/docs/search.json');
    }

    public function test_it_adds_all_files_to_search_index()
    {
        Filesystem::touch('_docs/foo.md');
        Filesystem::touch('_docs/bar.md');
        Filesystem::touch('_docs/baz.md');

        $this->assertCount(3, (new DocumentationSearchService())->run()->searchIndex);

        Filesystem::unlink('_docs/foo.md');
        Filesystem::unlink('_docs/bar.md');
        Filesystem::unlink('_docs/baz.md');
    }

    public function test_it_handles_generation_even_when_there_are_no_pages()
    {
        DocumentationSearchService::generate();

        $this->assertSame('[]', file_get_contents(DocumentationSearchService::$filePath));

        Filesystem::unlink('_site/docs/search.json');
    }

    public function test_save_method_saves_the_file_to_the_correct_location()
    {
        DocumentationSearchService::generate();

        $this->assertFileExists('_site/docs/search.json');

        Filesystem::unlink('_site/docs/search.json');
    }

    public function test_generate_page_entry_method_generates_a_page_entry()
    {
        Filesystem::putContents('_docs/foo.md', "# Bar\n\n Hello World");

        $this->assertSame([
            'slug' => 'foo',
            'title' => 'Bar',
            'content' => "Bar \n Hello World",
            'destination' => 'foo.html',
        ], (new DocumentationSearchService())->generatePageEntry(DocumentationPage::parse('foo')));

        Filesystem::unlink('_docs/foo.md');
    }

    public function test_it_generates_a_valid_JSON()
    {
        Filesystem::putContents('_docs/foo.md', "# Bar\n\n Hello World");
        Filesystem::putContents('_docs/bar.md', "# Foo\n\n Hello World");

        $this->assertSame(
            '[{"slug":"bar","title":"Foo","content":"Foo \n Hello World","destination":"bar.html"},'.
            '{"slug":"foo","title":"Bar","content":"Bar \n Hello World","destination":"foo.html"}]',
            json_encode((new DocumentationSearchService())->run()->searchIndex->toArray())
        );

        Filesystem::unlink('_docs/foo.md');
        Filesystem::unlink('_docs/bar.md');
    }

    public function test_get_destination_for_slug_returns_empty_string_for_index_when_pretty_url_is_enabled()
    {
        config(['site.pretty_urls' => true]);

        $this->assertSame('',
            (new DocumentationSearchService())->getDestinationForSlug('index')
        );
    }

    public function test_get_destination_for_slug_returns_pretty_url_when_enabled()
    {
        config(['site.pretty_urls' => true]);

        $this->assertSame('foo',
            (new DocumentationSearchService())->getDestinationForSlug('foo')
        );
    }

    public function test_excluded_pages_are_not_present_in_the_search_index()
    {
        Filesystem::touch(('_docs/excluded.md'));
        config(['docs.exclude_from_search' => ['excluded']]);

        $this->assertStringNotContainsString('excluded',
            json_encode((new DocumentationSearchService())->run()->searchIndex->toArray())
        );

        Filesystem::unlink('_docs/excluded.md');
    }

    public function test_nested_source_files_do_not_retain_directory_name_in_search_index()
    {
        Filesystem::makeDirectory(Hyde::path('_docs/foo'));
        Filesystem::touch('_docs/foo/bar.md');

        $this->assertStringNotContainsString('foo',
            json_encode((new DocumentationSearchService())->run()->searchIndex->toArray())
        );

        Filesystem::deleteDirectory('_docs/foo');
    }
}
