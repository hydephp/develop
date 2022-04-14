<?php

namespace Hyde\RealtimeCompiler\Actions;

class FormatsAnsiString
{
    private string $inputString;
    private string $outputString;

    protected static array $colors = [
        'error' => "\033[0;91m",
        'warning' => "\033[0;93m",
        'info' => "\033[0;94m",
        'success' => "\033[0;92m",
    ];

    protected static array $actions = [
        'SourceFileFinder',
        'CompilesSourceFile',
        'FormatsAnsiString',
    ];

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

    public static function get(string $string)
    {
        return (new self($string))->getOutputString();
    }

    private function canTokenize()
    {
        // Check if the string contains a word followed by ': ' and then a string
        return preg_match('/^[a-zA-Z0-9_]+: /', $this->inputString);
    }

    private function tokenize()
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
        if (in_array($string, static::$actions)) {
            return $string;
        }

        // Gold (yellow) color
        return "\033[0;33m" . $string . "\033[0m";
    }
}