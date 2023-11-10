<?php

declare(strict_types=1);

namespace Hyde\RealtimeCompiler;

use Closure;
use Hyde\Hyde;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;

use function max;
use function str;
use function trim;
use function strlen;
use function substr;
use function sprintf;
use function str_repeat;
use function str_replace;
use function Termwind\render;

class ConsoleOutput
{
    protected bool $verbose;

    public static function printStartMessage(string $host, int $port): void
    {
        $title = 'HydePHP Realtime Compiler';
        $version = ' v'.Hyde::version();

        $url = sprintf('http://%s:%d', $host, $port);

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

    public static function getFormatter(bool $verbose): Closure
    {
        $console = (new static($verbose));

        return function (string $type, string $line) use ($console): void {
            $console->handleOutput($line);
        };
    }

    public function __construct(bool $verbose = false)
    {
        $this->verbose = $verbose;
    }

    protected function handleOutput(string $buffer): void
    {
        str($buffer)->trim()->explode("\n")->each(function (string $line): void {
            $line = $this->formatLineForOutput($line);

            if ($line !== null) {
                render($line);
            }
        });
    }

    protected function formatLineForOutput(string $line): ?string
    {
        if (str_contains($line, 'Development Server (http:')) {
            return $this->formatServerStartedLine($line);
        }
        if (str_contains($line, ']: ')) {
            return $this->formatRequestLine($line);
        }
        if (str_ends_with(trim($line), 'Accepted') || str_ends_with(trim($line), 'Closing')) {
            if ($this->verbose) {
                return $this->formatRequestStatusLine($line);
            } else {
                return null;
            }
        }

        return $this->formatLine($line, Carbon::now());
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
}
