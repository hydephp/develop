<?php

namespace Desilva\Console\Contracts;

interface Console
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