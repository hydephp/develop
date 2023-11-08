<?php

declare(strict_types=1);

namespace Hyde\Console\Commands;

use Hyde\Hyde;
use Hyde\Facades\Config;
use Illuminate\Support\Facades\Process;
use LaravelZero\Framework\Commands\Command;

use function Termwind\{render};
use function sprintf;

/**
 * Start the realtime compiler server.
 *
 * @see https://github.com/hydephp/realtime-compiler
 */
class ServeCommand extends Command
{
    /** @var string */
    protected $signature = 'serve {--host= : <comment>[default: "localhost"]</comment>}} {--port= : <comment>[default: 8080]</comment>} {--fancy= : <comment>[default: true]</comment>}';

    /** @var string */
    protected $description = 'Start the realtime compiler server.';

    public function handle(): int
    {
        $this->printStartMessage();

        $this->runServerProcess(sprintf('php -S %s:%d %s',
            $this->getHostSelection(),
            $this->getPortSelection(),
            $this->getExecutablePath()
        ));

        return Command::SUCCESS;
    }

    protected function getPortSelection(): int
    {
        return (int) ($this->option('port') ?: Config::getInt('hyde.server.port', 8080));
    }

    protected function getHostSelection(): string
    {
        return (string) $this->option('host') ?: Config::getString('hyde.server.host', 'localhost');
    }

    protected function getExecutablePath(): string
    {
        return Hyde::path('vendor/hyde/realtime-compiler/bin/server.php');
    }

    /** @codeCoverageIgnore Until output is testable */
    protected function runServerProcess(string $command): void
    {
        Process::forever()->run($command, function (string $type, string $line): void {
            $this->option('fancy') ? $this->handleOutput($line) : $this->output->write($line);
        });
    }

    protected function printStartMessage(): void
    {
        if ($this->option('fancy')) {
            $this->line('<info>Starting the HydeRC server...</info> Press Ctrl+C to stop');
        } else {
            $title = 'HydePHP Realtime Compiler';
            $version = ' v'.Hyde::version();

            $url = sprintf('http://%s:%d', $this->getHostSelection(), $this->getPortSelection());

            $width = max(strlen("$title $version"), strlen("Listening on $url") + 1) + 1;
            $spacing = str_repeat('&nbsp;', $width);
            $lines = str_repeat('─', $width);

            $line1 = '&nbsp;'.sprintf('<span class="text-blue-500">%s</span>&nbsp;<span class="text-gray">%s</span>', $title, $version).str_repeat('&nbsp;', $width - strlen("$title $version"));
            $line2 = '&nbsp;'.sprintf('<span class="text-white">Listening on </span>&nbsp;<a href="%s" class="text-yellow-500">%s</a>', $url, $url).str_repeat('&nbsp;', $width - strlen("Listening on $url") - 1);
            render(<<<HTML
<div class="text-green-500">
<br>
&nbsp;╭{$lines}╮<br>
&nbsp;│{$spacing}│<br>
&nbsp;│{$line1}│<br>
&nbsp;│{$spacing}│<br>
&nbsp;│{$line2}│<br>
&nbsp;│{$spacing}│<br>
&nbsp;╰{$lines}╯
<br>
</div>
HTML);
        }
    }

    protected function handleOutput(string $line): void
    {
        $isRequestLine = str_ends_with(trim($line), 'Accepted') || str_ends_with(trim($line), 'Closing');

        if ($isRequestLine && ! $this->output->isVerbose()) {
            return;
        }

        $this->output->write($line);
    }
}
