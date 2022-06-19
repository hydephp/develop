<?php

namespace Hyde\Testing\Framework\Unit;

use Hyde\Framework\Models\DocumentationPage;
use Hyde\Testing\TestCase;

/**
 * @covers \Hyde\Framework\Models\DocumentationPage
 */
class DocumentationPageTest extends TestCase
{
    // Test documentation page table of contents is generated automatically.
    public function testCanGenerateTableOfContents()
    {
        $page = (new DocumentationPage([], '# Foo'));
        $this->assertIsString($page->tableOfContents);
    }

    // Test getCurrentPagePath returns trimmed path to current page slug in documentation output directory.
    public function testCanGetCurrentPagePath()
    {
        $page = (new DocumentationPage([], '', '', 'foo'));
        $this->assertEquals('docs/foo', $page->getCurrentPagePath());

        config(['docs.output_directory' => 'documentation/latest/']);
        $this->assertEquals('documentation/latest/foo', $page->getCurrentPagePath());
    }

    // Test getOnlineSourcePath returns false if source file location base is not set.
    public function testCanGetOnlineSourcePath()
    {
        $page = (new DocumentationPage([], ''));
        $this->assertFalse($page->getOnlineSourcePath());
    }

    // Test getOnlineSourcePath returns proper source path to current page
    public function testCanGetOnlineSourcePathWithSourceFileLocationBase()
    {
        config(['docs.source_file_location_base' => 'docs.example.com/edit']);
        $page = (new DocumentationPage([], '', '', 'foo'));
        $this->assertEquals('docs.example.com/edit/foo.md', $page->getOnlineSourcePath());
    }

    // Test getOnlineSourcePath trims base input to avoid trailing slash.
    public function testCanGetOnlineSourcePathWithTrailingSlash()
    {
        $page = (new DocumentationPage([], '', '', 'foo'));

        config(['docs.source_file_location_base' => 'edit/']);
        $this->assertEquals('edit/foo.md', $page->getOnlineSourcePath());

        config(['docs.source_file_location_base' => 'edit']);
        $this->assertEquals('edit/foo.md', $page->getOnlineSourcePath());
    }

    // Test getDocumentationOutputPath helper returns path to documentation output directory.
    public function testCanGetDocumentationOutputPath()
    {
        $this->assertEquals('docs', DocumentationPage::getDocumentationOutputPath());
    }

    // Test getDocumentationOutputPath helper can be customized.
    public function testCanGetDocumentationOutputPathWithCustomOutputDirectory()
    {
        config(['docs.output_directory' => 'foo']);
        $this->assertEquals('foo', DocumentationPage::getDocumentationOutputPath());
    }

    // Test getDocumentationOutputPath helper trims trailing slashes.
    public function testCanGetDocumentationOutputPathWithTrailingSlashes()
    {
        $tests = [
            'foo',
            'foo/',
            'foo//',
            'foo\\',
            '/foo/',
        ];

        foreach ($tests as $test) {
            config(['docs.output_directory' => $test]);
            $this->assertEquals('foo', DocumentationPage::getDocumentationOutputPath());
        }
    }
}
