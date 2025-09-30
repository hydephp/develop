<?php

declare(strict_types=1);

namespace Hyde\Testing;

use Hyde\Hyde;

use function config;
use function array_shift;
use function file_get_contents;
use function Hyde\normalize_newlines;

trait FluentTestingHelpers
{
    protected function assertFileEqualsString(string $string, string $path, bool $strict = false): void
    {
        if ($strict) {
            $this->assertSame($string, file_get_contents(Hyde::path($path)));
        } else {
            $this->assertEquals(normalize_newlines($string), normalize_newlines(file_get_contents(Hyde::path($path))));
        }
    }

    /**
     * Disable the throwing of exceptions on console commands for the duration of the test.
     *
     * Note that this only affects commands using the {@see \Hyde\Console\Concerns\Command::safeHandle()} method.
     */
    protected function throwOnConsoleException(bool $throw = true): void
    {
        config(['app.throw_on_console_exception' => $throw]);
    }

    /**
     * Set the site URL for the duration of the test.
     */
    protected function withSiteUrl(?string $url = 'https://example.com'): void
    {
        if ($this instanceof UnitTestCase) {
            self::mockConfig(['hyde.url' => $url]);
        } else {
            config(['hyde.url' => $url]);
        }
    }

    /**
     * Remove the site URL for the duration of the test.
     */
    protected function withoutSiteUrl(): void
    {
        $this->withSiteUrl(null);
    }

    /** Assert that all the given variables are the same. */
    protected function assertAllSame(...$vars): void
    {
        $first = array_shift($vars);

        foreach ($vars as $var) {
            $this->assertEquals($first, $var);
            $this->assertSame($first, $var);
        }
    }

    protected function dd($var): void
    {
        if (is_string($var)) {
            echo "```\n";
            echo $var.($var[-1] === "\n" ? '' : "\n");
            echo "```\n";
        } elseif (is_array($var)) {
            echo "```php\n";
            echo $this->formatArray($var);
            echo "```\n";
        } else {
            dd($var);
        }

        exit;
    }

    private function formatArray(array $array): string
    {
        $json = json_encode($array, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

        // Transform JSON to PHP array syntax
        $php = preg_replace('/^(\s*)\"(\w+)\":/m', '$1$2:', $json); // Remove quotes from keys
        $php = str_replace('{', '[', $php); // Replace { with [
        $php = str_replace('}', ']', $php); // Replace } with ]

        return $php."\n";
    }
}
