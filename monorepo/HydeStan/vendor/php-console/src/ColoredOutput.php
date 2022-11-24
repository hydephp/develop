<?php

namespace Desilva\Console;

trait ColoredOutput
{
    protected function ansi(string $string, string $color): string
    {
        return "\033[" . $color . "m" . $string . "\033[0m";
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