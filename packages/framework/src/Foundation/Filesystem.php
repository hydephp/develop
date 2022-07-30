<?php

namespace Hyde\Framework\Foundation;

class Filesystem
{
    protected string $basePath;

    public function __construct(string $basePath)
    {
        $this->basePath = $basePath;
    }
}
