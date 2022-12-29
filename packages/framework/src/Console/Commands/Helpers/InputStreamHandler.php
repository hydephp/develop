<?php

declare(strict_types=1);

namespace Hyde\Console\Commands\Helpers;

use function array_shift;
use function explode;
use function fgets;
use Hyde\Framework\Concerns\InvokableAction;
use Hyde\Hyde;
use function trim;

/**
 * Collects an array of lines from the standard input stream. Feed is terminated by a blank line.
 *
 * @see \Hyde\Framework\Testing\Unit\InputStreamHandlerTest
 */
class InputStreamHandler extends InvokableAction
{
    private static ?array $mockedStreamBuffer = null;

    public function __invoke(): array
    {
        return $this->getLinesFromInputStream();
    }

    protected function getLinesFromInputStream(): array
    {
        $lines = [];
        do {
            $line = Hyde::stripNewlines($this->readInputStream());
            if ($line === '') {
                break;
            }
            $lines[] = trim($line);
        } while (true);

        return $lines;
    }

    /** @codeCoverageIgnore Allows for mocking of the standard input stream */
    protected function readInputStream(): string
    {
        if (self::$mockedStreamBuffer !== null) {
            return array_shift(self::$mockedStreamBuffer) ?? '';
        }

        return fgets(STDIN);
    }

    /** @internal Allows for mocking of the standard input stream */
    public static function mockInput(string $input): void
    {
        self::$mockedStreamBuffer = explode("\n", $input);
    }

    /** Format a consistent message that can be output to the console */
    public static function formatMessage(string $name, string $type = 'values'): string
    {
        return "<info>Enter $type for field </>[<comment>$name</comment>] (end with an empty line)";
    }
}
