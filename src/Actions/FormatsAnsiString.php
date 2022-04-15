<?php

namespace Hyde\RealtimeCompiler\Actions;

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

    public static function get(string $string): string
    {
        return (new self($string))->getOutputString();
    }

    private function canTokenize(): bool
    {
        // Check if the string contains a word followed by ': ' and then a string
        return preg_match('/^[a-zA-Z0-9_]+: /', $this->inputString);
    }

    private function tokenize(): string
    {
        $tokens = explode(': ', $this->inputString);

        $this->outputString = static::lightGray('[') . static::main($tokens[0]) . static::lightGray(']');

        if (isset($tokens[1])) {
            $this->outputString .= ': ' . $tokens[1];
        }

        return $this->outputString;
    }

    private static function lightGray(string $string): string
    {
        return "\033[0;90m" . $string . "\033[0m";
    }

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
}