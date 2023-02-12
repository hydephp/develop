<?php

declare(strict_types=1);

namespace Hyde\Markdown\Processing;

use Hyde\Markdown\Contracts\MarkdownShortcodeContract;
use Hyde\Markdown\Models\Markdown;

/**
 * @see \Hyde\Framework\Testing\Feature\ColoredBlockquoteShortcodesTest
 */
abstract class ColoredBlockquotes implements MarkdownShortcodeContract
{
    protected static string $signature = '>color';

    public static function signature(): string
    {
        return static::$signature;
    }

    public static function resolve(string $input): string
    {
        return str_starts_with($input, static::signature())
            ? static::expand($input)
            : $input;
    }

    protected static function expand(string $input): string
    {
        $template = '<blockquote class="%s">%s</blockquote>';
        $signature = static::getClassNameFromSignature(static::signature());
        $value = trim(Markdown::render(trim(substr($input, strlen(static::signature())), ' ')));

        return sprintf(
            $template,
            $signature,
            $value
        );
    }

    protected static function getClassNameFromSignature(string $signature): string
    {
        return str_replace('>', '', $signature);
    }

    /** @return ColoredBlockquotes[] */
    public static function get(): array
    {
        return [
            new class extends ColoredBlockquotes
            {
                protected static string $signature = '>danger';
            },
            new class extends ColoredBlockquotes
            {
                protected static string $signature = '>info';
            },
            new class extends ColoredBlockquotes
            {
                protected static string $signature = '>success';
            },
            new class extends ColoredBlockquotes
            {
                protected static string $signature = '>warning';
            },
        ];
    }
}
