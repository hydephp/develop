<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature;

use Closure;
use Hyde\Console\Concerns\Command;
use Hyde\Hyde;
use Hyde\Testing\TestCase;
use Symfony\Component\Console\Style\OutputStyle;

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

    public function test_create_clickable_filepath_creates_link_for_non_existing_file()
    {
        $this->assertSame(
            sprintf('file://%s/foo.txt', str_replace('\\', '/', Hyde::path())),
            Command::createClickableFilepath('foo.txt')
        );
    }

    public function testInfoComment()
    {
        $command = new MockableTestCommand();
        $command->closure = function (Command $command) {
            $command->infoComment('foo', 'bar');
        };

        $output = $this->mock(OutputStyle::class);
        $output->shouldReceive('writeln')->once()->withArgs(function (string $message): bool {
            return $message === '<info>foo</info> [<comment>bar</comment>]';
        });

        $command->setMockedOutput($output);
        $command->handle();
    }

    public function testInfoCommentWithExtraInfo()
    {
        $command = new MockableTestCommand();
        $command->closure = function (Command $command) {
            $command->infoComment('foo', 'bar', 'baz');
        };

        $output = $this->mock(OutputStyle::class);
        $output->shouldReceive('writeln')->once()->withArgs(function (string $message): bool {
            return $message === '<info>foo</info> [<comment>bar</comment>] <info>baz</info>';
        });

        $command->setMockedOutput($output);
        $command->handle();
    }
}

class MockableTestCommand extends Command
{
    public Closure $closure;

    public function handle(): int
    {
        ($this->closure)($this);

        return 0;
    }

    public function setMockedOutput($output)
    {
        $this->output = $output;
    }
}
