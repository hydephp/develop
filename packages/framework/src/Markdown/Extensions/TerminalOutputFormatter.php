<?php

declare(strict_types=1);

namespace Hyde\Markdown\Extensions;

use function array_pad;
use function array_pop;
use function count;
use function e;
use function end;
use function explode;
use function implode;
use function in_array;
use function preg_match;
use function preg_split;
use function str_repeat;
use function str_starts_with;
use function strtolower;
use function substr;

/**
 * Renders the formatting tags of a terminal block line as styled markup.
 *
 * @internal
 */
class TerminalOutputFormatter
{
    protected const TAG_PATTERN = '/(\\\\?<\/?[a-z][^<>]*>|\\\\?<\/>)/i';

    protected const STYLES = ['info', 'comment', 'question', 'error'];

    protected const COLORS = [
        'black', 'red', 'green', 'yellow', 'blue', 'magenta', 'cyan', 'white', 'gray',
        'bright-red', 'bright-green', 'bright-yellow', 'bright-blue',
        'bright-magenta', 'bright-cyan', 'bright-white',
    ];

    protected const OPTIONS = ['bold', 'underscore', 'strikethrough'];

    public function format(string $text): string
    {
        $output = '';
        $stack = [];

        foreach (preg_split(static::TAG_PATTERN, $text, -1, PREG_SPLIT_DELIM_CAPTURE) ?: [] as $part) {
            if (str_starts_with($part, '\\')) {
                $output .= e($this->unescapeTag($part));
            } elseif (preg_match('/^<([a-z][^<>]*)>$/i', $part, $matches) && ($classes = $this->resolveStyle($matches[1])) !== null) {
                $stack[] = $matches[1];
                $output .= '<span class="'.$classes.'">';
            } elseif ($this->closesOpenTag($part, $stack)) {
                array_pop($stack);
                $output .= '</span>';
            } else {
                $output .= e($part);
            }
        }

        return $output.str_repeat('</span>', count($stack));
    }

    /**
     * A backslash only escapes recognized formatting syntax, since `\<` is ordinary
     * shell and regular expression syntax that a terminal block has to leave alone.
     */
    protected function unescapeTag(string $part): string
    {
        $tag = substr($part, 1);

        return $this->isStyleTag($tag) ? $tag : $part;
    }

    protected function isStyleTag(string $tag): bool
    {
        return $tag === '</>'
            || (preg_match('/^<\/?([a-z][^<>]*)>$/i', $tag, $matches) && $this->resolveStyle($matches[1]) !== null);
    }

    /** @param  array<int, string>  $stack */
    protected function closesOpenTag(string $part, array $stack): bool
    {
        return $stack !== [] && ($part === '</>' || $part === '</'.end($stack).'>');
    }

    /** @return string|null The classes to style the tag with, or null when it is not a style tag. */
    protected function resolveStyle(string $tag): ?string
    {
        return in_array($tag, static::STYLES, true) ? 'hyde-terminal-'.$tag : $this->resolveInlineStyle($tag);
    }

    protected function resolveInlineStyle(string $tag): ?string
    {
        $classes = [];

        foreach (explode(';', $tag) as $pair) {
            [$attribute, $value] = array_pad(explode('=', $pair, 2), 2, null);

            if ($value === null) {
                return null;
            }

            $attribute = strtolower($attribute);
            $resolved = $this->resolveAttribute($attribute, strtolower($value));

            if ($resolved === null) {
                return null;
            }

            $classes[$attribute] = $resolved;
        }

        return implode(' ', $classes);
    }

    protected function resolveAttribute(string $attribute, string $value): ?string
    {
        return match ($attribute) {
            'fg', 'bg' => in_array($value, static::COLORS, true) ? "hyde-terminal-$attribute-$value" : null,
            'options' => $this->resolveOptions($value),
            default => null,
        };
    }

    protected function resolveOptions(string $value): ?string
    {
        $classes = [];

        foreach (explode(',', $value) as $option) {
            if (! in_array($option, static::OPTIONS, true)) {
                return null;
            }

            $classes[$option] = 'hyde-terminal-'.$option;
        }

        return implode(' ', $classes);
    }
}
