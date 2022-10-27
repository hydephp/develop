<?php

declare(strict_types=1);

namespace Hyde\Markdown\Contracts;

/**
 * @see \Hyde\Markdown\ShortcodeProcessor to see how this is used.
 */
interface MarkdownShortcodeContract
{
    public static function signature(): string;

    public static function resolve(string $input): string;
}
