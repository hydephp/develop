<?php

declare(strict_types=1);

namespace Hyde\Console\Commands;

use Closure;
use Hyde\Hyde;
use Hyde\Facades\Config;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;
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
    protected $signature = 'serve {--host= : <comment>[default: "localhost"]</comment>}} {--port= : <comment>[default: 8080]</comment>}';

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

    protected function runServerProcess(string $command): void
    {
        $outputHandler = $this->useBasicOutput() ? $this->getDefaultOutputHandler() : $this->getFancyOutputHandler();

        Process::forever()->run($command, $outputHandler);
    }

    protected function printStartMessage(): void
    {
        if ($this->useBasicOutput()) {
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

    protected function handleOutput(string $buffer): void
    {
        str($buffer)->trim()->explode("\n")->each(function (string $line): void {
            if (str_contains($line, 'Development Server (http:')) {
                $line = $this->formatServerStartedLine($line);
            } elseif (str_contains($line, ']: ')) {
                $line = $this->formatRequestLine($line);
            } elseif (str_ends_with(trim($line), 'Accepted') || str_ends_with(trim($line), 'Closing')) {
                if ($this->output->isVerbose()) {
                    $line = $this->formatRequestStatusLine($line);
                } else {
                    return;
                }
            } else {
                $line = $this->formatLine($line, Carbon::now());
            }

            render($line);
        });
    }

    protected function formatServerStartedLine(string $line): string
    {
        return $this->formatLine(sprintf('PHP %s Development Server started. <span class="text-yellow-500">Press Ctrl+C to stop.</span>', PHP_VERSION), $this->parseDate($line));
    }

    protected function formatRequestLine(string $line): string
    {
        $dateString = Str::betweenFirst($line, '[', ']');
        $message = substr($line, strlen($dateString) + 3);

        $statusCode = Str::between($message, ' [', ']:');
        if ($statusCode >= 400) {
            $message = str_replace($statusCode, sprintf('<span class="text-red-500">%s</span>', $statusCode), $message);
            $iconColor = 'yellow-500';
        }

        return $this->formatLine($message, $this->parseDate($line), $iconColor ?? 'green-500');
    }

    protected function formatRequestStatusLine(string $line): string
    {
        $address = trim(Str::between($line, ']', ' '));
        $status = str_contains($line, 'Accepted') ? 'Accepted' : 'Closing';

        return $this->formatLine(sprintf('%s %s', $address, $status), $this->parseDate($line));
    }

    protected function formatLine(string $message, Carbon $date, string $iconColor = 'blue-500'): string
    {
        return sprintf(<<<'HTML'
            <div class="flex w-full justify-between">
                <span>
                    <span class="text-%s">i</span>
                    %s
                </span>
                <span class="text-gray">%s</span>
            </div>
            HTML,
            $iconColor, $message, $date->format('Y-m-d H:i:s')
        );
    }

    protected function parseDate(string $line): Carbon
    {
        return Carbon::parse(Str::betweenFirst($line, '[', ']'));
    }

    protected function getDefaultOutputHandler(): Closure
    {
        return function (string $type, string $line): void {
            $this->output->write($line);
        };
    }

    protected function getFancyOutputHandler(): Closure
    {
        return function (string $type, string $line): void {
            $this->handleOutput($line);
        };
    }

    protected function useBasicOutput(): bool
    {
        return $this->option('no-ansi');
    }
}
