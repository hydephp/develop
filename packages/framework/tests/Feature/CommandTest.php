<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature;

use Hyde\Console\Concerns\Command;
use Hyde\Testing\TestCase;

/**
 * @covers \Hyde\Console\Concerns\Command
 */
class CommandTest extends TestCase
{
    public function test_create_clickable_filepath_creates_link_for_existing_file()
    {
        $filename = 'be2329d7-3596-48f4-b5b8-deff352246a9';
        touch($filename);
        $output = Command::createClickableFilepath($filename);
        $this->assertStringContainsString('file://', $output);
        $this->assertStringContainsString($filename, $output);
        unlink($filename);
    }

    public function test_create_clickable_filepath_falls_back_to_returning_input_if_file_does_not_exist()
    {
        $filename = 'be2329d7-3596-48f4-b5b8-deff352246a9';
        $output = Command::createClickableFilepath($filename);
        $this->assertSame($filename, $output);
    }
}
