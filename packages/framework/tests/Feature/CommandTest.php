<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature;

use Hyde\Console\Concerns\Command;
use Hyde\Hyde;
use Hyde\Testing\TestCase;

/**
 * @covers \Hyde\Console\Concerns\Command
 */
class CommandTest extends TestCase
{
    public function test_create_clickable_filepath_creates_link_for_existing_file()
    {
        $this->file('foo.txt');

        $this->assertSame(
            sprintf('file://%s/foo.txt', str_replace('\\', '/', Hyde::path())),
            Command::createClickableFilepath('foo.txt')
        );
    }

    public function test_create_clickable_filepath_falls_back_to_returning_input_if_file_does_not_exist()
    {
        $this->assertSame('foo.txt', Command::createClickableFilepath('foo.txt'));
    }
}
