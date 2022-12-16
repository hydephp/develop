<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit;

use Hyde\Console\Commands\Helpers\InputStreamHandler;
use Hyde\Testing\TestCase;
use Illuminate\Console\Command;
use Illuminate\Console\OutputStyle;
use Mockery;

use function implode;

/**
 * @covers \Hyde\Console\Commands\Helpers\InputStreamHandler
 */
class InputStreamHandlerTest extends TestCase
{
    public function testCanCollectInput()
    {
        InputStreamHandler::mockInput('foo');

        $command = new TestCommand ;

        $output = Mockery::mock(OutputStyle::class);
        $output->shouldReceive('writeln')->once()->withArgs(function (string $message) {
            return $message === 'foo';
        });
        $command->setOutput($output);
        $this->assertSame(0, $command->handle());
    }
}

class TestCommand extends Command {
    public function handle(): int
    {
        $this->output->writeln(implode(', ', InputStreamHandler::call()));

        return 0;
    }
}
