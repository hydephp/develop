<?php

namespace Hyde\Framework\Testing\Feature;

use Hyde\Framework\Hyde;
use Hyde\Framework\Models\Pages\DocumentationPage;
use Hyde\Testing\TestCase;

class HydeDocsIndexPathTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->clearBoth();
    }

    protected function tearDown(): void
    {
        $this->clearBoth();

        parent::tearDown();
    }

    public function test_returns_false_if_no_index_or_readme_exists()
    {
        $this->assertEquals(false, Hyde::docsIndexPath());
    }

    public function test_returns_readme_if_only_readme_exists()
    {
        $this->setReadme();
        $this->assertEquals('docs/readme.html', Hyde::docsIndexPath());
    }

    public function test_returns_index_if_both_readme_and_index_exists()
    {
        $this->setReadme();
        $this->setIndex();
        $this->assertEquals('docs/index.html', Hyde::docsIndexPath());
    }

    public function test_returns_index_if_only_index_exist()
    {
        $this->setIndex();
        $this->assertEquals('docs/index.html', Hyde::docsIndexPath());
    }

    public function test_helper_can_find_index_path_when_custom_docs_directory_is_used()
    {
        mkdir(Hyde::path('foo'));
        file_put_contents(Hyde::path('foo/index.md'), '');

        DocumentationPage::$sourceDirectory = 'foo';
        $this->assertEquals('docs/index.html', Hyde::docsIndexPath());

        unlink(Hyde::path('foo/index.md'));
        rmdir(Hyde::path('foo'));
    }

    protected function setReadme()
    {
        file_put_contents(Hyde::path('_docs/readme.md'), '');
    }

    protected function setIndex()
    {
        file_put_contents(Hyde::path('_docs/index.md'), '');
    }

    protected function clearBoth()
    {
        @unlink(Hyde::path('_docs/index.md'));
        @unlink(Hyde::path('_docs/readme.md'));
    }
}
