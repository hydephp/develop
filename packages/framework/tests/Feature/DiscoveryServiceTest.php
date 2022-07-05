<?php

namespace Hyde\Framework\Testing\Feature;

use Hyde\Framework\Hyde;
use Hyde\Framework\Models\Pages\BladePage;
use Hyde\Framework\Models\Pages\DocumentationPage;
use Hyde\Framework\Models\Pages\MarkdownPage;
use Hyde\Framework\Models\Pages\MarkdownPost;
use Hyde\Framework\Models\Parsers\DocumentationPageParser;
use Hyde\Framework\Models\Parsers\MarkdownPageParser;
use Hyde\Framework\Models\Parsers\MarkdownPostParser;
use Hyde\Framework\Services\DiscoveryService;
use Hyde\Testing\TestCase;

class DiscoveryServiceTest extends TestCase
{
    public function createContentSourceTestFiles()
    {
        Hyde::touch((DiscoveryService::getFilePathForModelClassFiles(MarkdownPost::class).'/test.md'));
        Hyde::touch((DiscoveryService::getFilePathForModelClassFiles(MarkdownPage::class).'/test.md'));
        Hyde::touch((DiscoveryService::getFilePathForModelClassFiles(DocumentationPage::class).'/test.md'));
        Hyde::touch((DiscoveryService::getFilePathForModelClassFiles(BladePage::class).'/test.blade.php'));
    }

    public function deleteContentSourceTestFiles()
    {
        unlink(Hyde::path(DiscoveryService::getFilePathForModelClassFiles(MarkdownPost::class).'/test.md'));
        unlink(Hyde::path(DiscoveryService::getFilePathForModelClassFiles(MarkdownPage::class).'/test.md'));
        unlink(Hyde::path(DiscoveryService::getFilePathForModelClassFiles(DocumentationPage::class).'/test.md'));
        unlink(Hyde::path(DiscoveryService::getFilePathForModelClassFiles(BladePage::class).'/test.blade.php'));
    }

    public function test_find_model_from_file_path()
    {
        $this->assertEquals(MarkdownPage::class, DiscoveryService::findModelFromFilePath('_pages/test.md'));
        $this->assertEquals(MarkdownPost::class, DiscoveryService::findModelFromFilePath('_posts/test.md'));
        $this->assertEquals(DocumentationPage::class, DiscoveryService::findModelFromFilePath('_docs/test.md'));
        $this->assertEquals(BladePage::class, DiscoveryService::findModelFromFilePath('_pages/test.blade.php'));

        $this->assertFalse(DiscoveryService::findModelFromFilePath('_foo/test.txt'));
    }

    public function test_get_parser_class_for_model()
    {
        $this->assertEquals(MarkdownPageParser::class, DiscoveryService::getParserClassForModel(MarkdownPage::class));
        $this->assertEquals(MarkdownPostParser::class, DiscoveryService::getParserClassForModel(MarkdownPost::class));
        $this->assertEquals(DocumentationPageParser::class, DiscoveryService::getParserClassForModel(DocumentationPage::class));
        $this->assertEquals(BladePage::class, DiscoveryService::getParserClassForModel(BladePage::class));
    }

    public function test_get_parser_instance_for_model()
    {
        $this->createContentSourceTestFiles();

        $this->assertInstanceOf(MarkdownPageParser::class, DiscoveryService::getParserInstanceForModel(MarkdownPage::class, 'test'));
        $this->assertInstanceOf(MarkdownPostParser::class, DiscoveryService::getParserInstanceForModel(MarkdownPost::class, 'test'));
        $this->assertInstanceOf(DocumentationPageParser::class, DiscoveryService::getParserInstanceForModel(DocumentationPage::class, 'test'));
        $this->assertInstanceOf(BladePage::class, DiscoveryService::getParserInstanceForModel(BladePage::class, 'test'));

        $this->deleteContentSourceTestFiles();
    }

    public function test_get_file_extension_for_model_files()
    {
        $this->assertEquals('.md', DiscoveryService::getFileExtensionForModelFiles(MarkdownPage::class));
        $this->assertEquals('.md', DiscoveryService::getFileExtensionForModelFiles(MarkdownPost::class));
        $this->assertEquals('.md', DiscoveryService::getFileExtensionForModelFiles(DocumentationPage::class));
        $this->assertEquals('.blade.php', DiscoveryService::getFileExtensionForModelFiles(BladePage::class));
    }

    public function test_get_file_path_for_model_class_files()
    {
        $this->assertEquals('_posts', DiscoveryService::getFilePathForModelClassFiles(MarkdownPost::class));
        $this->assertEquals('_pages', DiscoveryService::getFilePathForModelClassFiles(MarkdownPage::class));
        $this->assertEquals('_docs', DiscoveryService::getFilePathForModelClassFiles(DocumentationPage::class));
        $this->assertEquals('_pages', DiscoveryService::getFilePathForModelClassFiles(BladePage::class));
    }

    public function test_create_clickable_filepath()
    {
        $filename = 'be2329d7-3596-48f4-b5b8-deff352246a9';
        touch($filename);
        $output = DiscoveryService::createClickableFilepath($filename);
        $this->assertStringContainsString('file://', $output);
        $this->assertStringContainsString($filename, $output);
        unlink($filename);
    }
}
