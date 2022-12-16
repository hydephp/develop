<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit;

use Hyde\Console\Commands\Helpers\InputStreamHandler;
use Hyde\Testing\TestCase;
use Illuminate\Console\Command;

use function implode;

/**
 * @covers \Hyde\Console\Commands\Helpers\InputStreamHandler
 */
class InputStreamHandlerTest extends TestCase
{
    //
}

class TestCommand extends Command {
    public function handle(): int
    {
        $this->output->writeln(implode(', ', InputStreamHandler::call()));

        return 0;
    }
}
