<?php

/**
 * Forked version that is simplified to work without Composer.
 *
 * @link https://github.com/caendesilva/php-console
 * @license MIT
 */

declare(strict_types=1);

interface ConsoleContract
{
    public function write(string $string): self;
    public function line(string $message = ''): self;
    public function format(string $message): self;
    public function newline(int $count = 1): self;

    public function info(string $message): self;
    public function warn(string $message): self;
    public function error(string $message): self;
    public function debug(string $message): self;
    public function debugComment(string $message): self;
}

class Console implements ConsoleContract
{
    use ColoredOutput;

    public function write(string $string): self
    {
        file_put_contents('php://stdout', $string);

        return $this;
    }

    public function line(string $message = ''): self
    {
        $this->write($message . PHP_EOL);

        return $this;
    }

    public function newline(int $count = 1): self
    {
        $this->line(str_repeat(PHP_EOL, $count - 1));

        return $this;
    }

    public function info(string $message): self
    {
        $this->line(sprintf('%s%s%s: %s',
                $this->gray('['),
                $this->green('Info'),
                $this->gray(']'),
                $message)
        );
        return $this;
    }

    public function warn(string $message): self
    {
        $this->line(sprintf('%s%s%s: %s',
                $this->gray('['),
                $this->yellow('Warning'),
                $this->gray(']'),
                $message)
        );

        return $this;
    }

    public function error(string $message): self
    {
        $this->line(sprintf('%s%s%s: %s',
                $this->gray('['),
                $this->red('Error'),
                $this->gray(']'),
                $message)
        );

        return $this;
    }

    public function debug(string $message): self
    {
        $this->line(sprintf('%s%s%s: %s',
                $this->gray('['),
                $this->lightGray('Debug'),
                $this->gray(']'),
                $message)
        );

        return $this;
    }

    public function debugComment(string $message): self
    {
        $this->line(sprintf('%s%s',
                $this->gray(' > '),
                $this->lightGray($message))
        );

        return $this;
    }

    /**
     * Automatically format a log message.
     * Example: 'Info: Hello World!' becomes '[Info]: Hello World!' with colors.
     * @internal May be removed in the future.
     */
    public function format(string $message): self
    {
        $this->line((new FormatsAnsiString($message))->getOutputString());

        return $this;
    }
}

trait ColoredOutput
{
    protected function ansi(string $string, string $color): string
    {
        return "\033[" . $color . 'm' . $string . "\033[0m";
    }

    protected function black(string $string): string
    {
        return $this->ansi($string, '30');
    }

    protected function red(string $string): string
    {
        return $this->ansi($string, '31');
    }

    protected function green(string $string): string
    {
        return $this->ansi($string, '32');
    }

    protected function yellow(string $string): string
    {
        return $this->ansi($string, '33');
    }

    protected function blue(string $string): string
    {
        return $this->ansi($string, '34');
    }

    protected function magenta(string $string): string
    {
        return $this->ansi($string, '35');
    }

    protected function cyan(string $string): string
    {
        return $this->ansi($string, '36');
    }

    protected function white(string $string): string
    {
        return $this->ansi($string, '37');
    }

    protected function gray(string $string): string
    {
        return $this->ansi($string, '90');
    }

    protected function lightGray(string $string): string
    {
        return $this->ansi($string, '37');
    }
}


/**
 * @internal May be removed in the future.
 * Ported from https://github.com/hydephp/realtime-compiler/blob/1.x/src/Server.php
 */
class FormatsAnsiString
{
    private string $inputString;
    private string $outputString;

    public function __construct(string $inputString)
    {
        $this->inputString = $inputString;

        $this->outputString = $this->execute();
    }

    private function execute(): string
    {
        if ($this->canTokenize()) {
            return $this->tokenize();
        }

        return $this->inputString;
    }

    public function getOutputString(): string
    {
        return $this->outputString;
    }

    private function canTokenize(): bool
    {
        // Check if the string contains a word followed by ': ' and then a string
        return preg_match('/^[a-zA-Z0-9_]+: /', $this->inputString);
    }

    private function tokenize(): string
    {
        $tokens = explode(': ', $this->inputString);

        // Implode the rest of the tokens
        $value = implode(': ', array_slice($tokens, 1));

        $this->outputString = static::lightGray('[') . static::main($tokens[0]) . static::lightGray(']');

        if (isset($tokens[1])) {
            $this->outputString .= ': ' . $value;
        }

        return $this->outputString;
    }

    private static function lightGray(string $string): string
    {
        return "\033[0;90m" . $string . "\033[0m";
    }

    /** The primary color */
    private static function main(string $string): string
    {
        // If token string is an action, use a less bright (visible) color
        if (in_array($string, [
            'SourceFileFinder',
            'CompilesSourceFile',
            'FormatsAnsiString',
        ])) {
            return $string;
        }

        // Gold (yellow) color
        return "\033[0;33m" . $string . "\033[0m";
    }

    public static function get(string $string): string
    {
        return (new self($string))->getOutputString();
    }
}
