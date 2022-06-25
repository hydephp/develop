<?php

namespace Hyde\Framework\Contracts;

interface RenderableContract
{
    public static function getViewKey(): string;

    public function getSourceFilePath(): string;

    public function getOutputFilePath(): string;
}
