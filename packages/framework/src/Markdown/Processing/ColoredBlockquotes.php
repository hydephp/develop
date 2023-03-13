<?php

declare(strict_types=1);

namespace Hyde\Markdown\Processing;

use Hyde\Markdown\Contracts\MarkdownShortcodeContract;
use Hyde\Markdown\Models\Markdown;

use function str_replace;
use function sprintf;
use function strlen;
use function substr;
use function trim;

/**
 * @see \Hyde\Framework\Testing\Feature\ColoredBlockquoteShortcodesTest
 *
 * @internal This class may be refactored to work with a single class instead of five, thus extending this class is discouraged.
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
        return sprintf(
            '<blockquote class="%s">%s</blockquote>',
            static::getClassNameFromSignature(static::signature()),
            trim(Markdown::render(trim(substr($input, strlen(static::signature())), ' ')))
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
            /** @internal */
            new class extends ColoredBlockquotes
            {
                protected static string $signature = '>danger';
            },
            /** @internal */
            new class extends ColoredBlockquotes
            {
                protected static string $signature = '>info';
            },
            /** @internal */
            new class extends ColoredBlockquotes
            {
                protected static string $signature = '>success';
            },
            /** @internal */
            new class extends ColoredBlockquotes
            {
                protected static string $signature = '>warning';
            },
        ];
    }
}
