<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature;

use Hyde\Framework\Services\DiscoveryService;
use Hyde\Hyde;
use Hyde\Pages\MarkdownPage;
use Hyde\Testing\TestCase;
use Illuminate\Support\Facades\File;

/**
 * Test the Markdown page parser.
 */
class MarkdownPageTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        backupDirectory(Hyde::path('_pages'));
        File::deleteDirectory(Hyde::path('_pages'));
        mkdir(Hyde::path('_pages'));

        file_put_contents(Hyde::path('_pages/test-post.md'), "# PHPUnit Test File \n Hello World!");
    }

    protected function tearDown(): void
    {
        restoreDirectory(Hyde::path('_pages'));

        parent::tearDown();
    }

    public function test_can_get_collection_of_slugs()
    {
        $array = DiscoveryService::getMarkdownPageFiles();

        $this->assertIsArray($array);
        $this->assertCount(1, $array);
        $this->assertArrayHasKey('test-post', array_flip($array));
    }

    public function test_created_model_contains_expected_data()
    {
        $page = MarkdownPage::parse('test-post');

        $this->assertEquals('PHPUnit Test File', $page->title);
        $this->assertEquals("# PHPUnit Test File \n Hello World!", $page->markdown);
        $this->assertEquals('test-post', $page->identifier);
    }
}
