<?php

namespace Hyde\Framework\Testing\Feature;

use Hyde\Testing\TestCase;
use Hyde\Framework\Models\MarkdownPage;

/**
 * Test the AbstractPage class.
 *
 * Since the class is abstract, we can't test it directly,
 * so we will use the MarkdownPage class as a proxy,
 * since it's the simplest implementation.
 *
 * @covers \Hyde\Framework\Contracts\AbstractPage
 */
class AbstractPageTest extends TestCase
{
    public function test_get_source_directory() {
        $this->markTestSkipped('TODO');
    }

    public function test_get_output_directory() {
        $this->markTestSkipped('TODO');
    }

    public function test_get_file_extension() {
        $this->markTestSkipped('TODO');
    }

    public function test_get_parser_class() {
        $this->markTestSkipped('TODO');
    }

    public function test_get_parser() {
        $this->markTestSkipped('TODO');
    }

    public function test_parse() {
        $this->markTestSkipped('TODO');
    }

    public function test_files() {
        $this->markTestSkipped('TODO');
    }

    public function test_all() {
        $this->markTestSkipped('TODO');
    }

    public function test_qualify_basename() {
        $this->markTestSkipped('TODO');
    }

    public function test_get_output_location() {
        $this->markTestSkipped('TODO');
    }

    public function test_get_current_page_path() {
        $this->markTestSkipped('TODO');
    }

    public function test_get_output_path() {
        $this->markTestSkipped('TODO');
    }
}
