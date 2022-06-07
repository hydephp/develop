<?php

namespace Hyde\Framework\Contracts;

interface MarkdownShortcodeContract
{
    public static function signature(): string;

    public static function resolve(string $input): string;
}
