<?php

declare(strict_types=1);

namespace Desilva\Console;

/**
 * Forked version that is simplified to work without Composer.
 *
 * @link https://github.com/emmadesilva/php-console
 * @license MIT
 * @internal
 */
class Console
{
    public function write(string $string): self
    {
        file_put_contents('php://stdout', $string);

        return $this;
    }

    public function line(string $message = ''): self
    {
        return $this->write($message . PHP_EOL);
    }

    public function newline(int $count = 1): self
    {
        return $this->line(str_repeat(PHP_EOL, $count - 1));
    }

    public function info(string $message): self
    {
        return $this->line(sprintf('%s%s%s: %s', $this->gray('['), $this->green('Info'), $this->gray(']'), $message));
    }

    public function warn(string $message): self
    {
        return $this->line(sprintf('%s%s%s: %s', $this->gray('['), $this->yellow('Warning'), $this->gray(']'), $message));
    }

    public function error(string $message): self
    {
        return $this->line(sprintf('%s%s%s: %s', $this->gray('['), $this->red('Error'), $this->gray(']'), $message));
    }

    public function debug(string $message): self
    {
        return $this->line(sprintf('%s%s%s: %s', $this->gray('['), $this->lightGray('Debug'), $this->gray(']'), $message));
    }

    public function debugComment(string $message): self
    {
        return $this->line(sprintf('%s%s', $this->gray(' > '), $this->lightGray($message)));
    }
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
