<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature\Services;

use Hyde\Facades\Filesystem;
use Hyde\Framework\Services\DocumentationSearchService;
use Hyde\Hyde;
use Hyde\Testing\TestCase;

/**
 * @covers \Hyde\Framework\Services\DocumentationSearchService
 */
class DocumentationSearchServiceTest extends TestCase
{
    public function test_it_generates_a_json_file_with_a_search_index()
    {
        $this->file('_docs/foo.md');

        DocumentationSearchService::generate();

        $this->assertSame(json_encode([[
            'slug' => 'foo',
            'title' => 'Foo',
            'content' => '',
            'destination' => 'foo.html',
        ]]), file_get_contents('_site/docs/search.json'));
    }

    public function test_it_adds_all_files_to_search_index()
    {
        $this->file('_docs/foo.md');
        $this->file('_docs/bar.md');
        $this->file('_docs/baz.md');

        $this->assertCount(3, $this->getArray());
    }

    public function test_it_handles_generation_even_when_there_are_no_pages()
    {
        DocumentationSearchService::generate();

        $this->assertSame('[]', file_get_contents('_site/docs/search.json'));

        Filesystem::unlink('_site/docs/search.json');
    }

    public function test_save_method_saves_the_file_to_the_correct_location()
    {
        DocumentationSearchService::generate();

        $this->assertFileExists('_site/docs/search.json');

        Filesystem::unlink('_site/docs/search.json');
    }

    public function test_it_generates_a_valid_JSON()
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

    public function test_it_strips_markdown()
    {
        $this->file('_docs/foo.md', "# Foo Bar\n**Hello** _World_");

        $this->assertSame(
            "Foo Bar\nHello World",
            ($this->getArray())[0]['content']
        );
    }

    public function test_get_destination_for_slug_returns_empty_string_for_index_when_pretty_url_is_enabled()
    {
        config(['hyde.pretty_urls' => true]);
        $this->file('_docs/index.md');

        $this->assertSame('',
            $this->getArray()[0]['destination']
        );
    }

    public function test_get_destination_for_slug_returns_pretty_url_when_enabled()
    {
        config(['hyde.pretty_urls' => true]);
        $this->file('_docs/foo.md');

        $this->assertSame('foo',
            $this->getArray()[0]['destination']
        );
    }

    public function test_excluded_pages_are_not_present_in_the_search_index()
    {
        $this->file('_docs/excluded.md');
        config(['docs.exclude_from_search' => ['excluded']]);

        $this->assertStringNotContainsString('excluded',
            json_encode($this->getArray())
        );
    }

    public function test_nested_source_files_do_not_retain_directory_name_in_search_index()
    {
        $this->directory(Hyde::path('_docs/foo'));
        $this->file('_docs/foo/bar.md');

        $this->assertStringNotContainsString('foo',
            json_encode($this->getArray())
        );
    }

    protected function getArray(): array
    {
        return (new DocumentationSearchService())->run()->searchIndex->toArray();
    }
}
