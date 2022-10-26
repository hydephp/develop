<?php
declare(strict_types=1);

namespace Tests\Benchmarks\CBench;

trait ConsoleHelpers
{
    protected function line(string $message = ''): self
    {
        if (! $this->silent) {
            echo $message.PHP_EOL;
        }

        return $this;
    }

    protected function info(string $message): self
    {
        $this->line("\033[32m".$message."\033[0m");

        return $this;
    }

    protected function warn(string $message): self
    {
        $this->line("\033[33m".$message."\033[0m");

        return $this;
    }

    protected function error(string $message): self
    {
        $this->line("\033[31m".$message."\033[0m");

        return $this;
    }

    protected function success(string $message): self
    {
        $this->line("\033[32m".$message."\033[0m");

        return $this;
    }

    protected function comment(string $message): self
    {
        $this->line("\033[37m".$message."\033[0m");

        return $this;
    }

    protected function debug(string $message): self
    {
        $this->line("\033[36m".$message."\033[0m");

        return $this;
    }

    protected function newline(int $count = 1): self
    {
        $this->line(str_repeat(PHP_EOL, $count - 1));

        return $this;
    }
}
