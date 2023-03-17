<?php

declare(strict_types=1);

namespace Hyde\Foundation\Concerns;

use Hyde\Framework\Services\MarkdownService;
use Hyde\Markdown\Models\Markdown;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;

use function str_ireplace;
use function str_replace;
use function strtoupper;
use function trim;
use function ucfirst;

/**
 * @internal Single-use trait for the HydeKernel class.
 *
 * @see \Hyde\Foundation\HydeKernel
 */
trait ImplementsStringHelpers
{
    public static function makeTitle(string $value): string
    {
        // Don't modify all-uppercase input
        if ($value === strtoupper($value)) {
            return $value;
        }

        $alwaysLowercase = ['a', 'an', 'the', 'in', 'on', 'by', 'with', 'of', 'and', 'or', 'but'];

        return ucfirst(str_ireplace(
            $alwaysLowercase,
            $alwaysLowercase,
            Str::headline($value)
        ));
    }

    public static function normalizeNewlines(string $string): string
    {
        return str_replace("\r\n", "\n", $string);
    }

    public static function stripNewlines(string $string): string
    {
        return str_replace(["\r\n", "\n"], '', $string);
    }

    public static function trimSlashes(string $string): string
    {
        return trim($string, '/\\');
    }

    public static function markdown(string $text, bool $normalizeIndentation = false): HtmlString
    {
        if ($normalizeIndentation) {
            $text = MarkdownService::normalizeIndentationLevel($text);
        }

        return new HtmlString(Markdown::render($text));
    }
}
