<?php

namespace Desilva\Console;

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