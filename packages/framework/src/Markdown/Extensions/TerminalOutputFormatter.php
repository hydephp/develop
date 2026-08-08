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
use function preg_match;
use function preg_split;
use function str_repeat;
use function strtolower;

/**
 * Renders the console formatter tags of a terminal block line as styled markup.
 *
 * @internal
 */
class TerminalOutputFormatter
{
    protected const TAG_PATTERN = '/(<\/?[a-z][^<>]*>|<\/>)/i';

    protected const STYLES = [
        'info' => 'hyde-terminal-info text-[#C3E88D]',
        'comment' => 'hyde-terminal-comment text-[#FFCB6B]',
        'question' => 'hyde-terminal-question text-[#89DDFF]',
        'error' => 'hyde-terminal-error font-semibold text-[#F07178]',
    ];

    protected const FOREGROUND_COLORS = [
        'black' => 'hyde-terminal-fg-black text-[#292D3E]',
        'red' => 'hyde-terminal-fg-red text-[#F07178]',
        'green' => 'hyde-terminal-fg-green text-[#C3E88D]',
        'yellow' => 'hyde-terminal-fg-yellow text-[#FFCB6B]',
        'blue' => 'hyde-terminal-fg-blue text-[#82AAFF]',
        'magenta' => 'hyde-terminal-fg-magenta text-[#C792EA]',
        'cyan' => 'hyde-terminal-fg-cyan text-[#89DDFF]',
        'white' => 'hyde-terminal-fg-white text-[#D0D0D0]',
        'gray' => 'hyde-terminal-fg-gray text-[#676E95]',
        'bright-red' => 'hyde-terminal-fg-bright-red text-[#FF8B92]',
        'bright-green' => 'hyde-terminal-fg-bright-green text-[#DDFFA7]',
        'bright-yellow' => 'hyde-terminal-fg-bright-yellow text-[#FFE585]',
        'bright-blue' => 'hyde-terminal-fg-bright-blue text-[#9CC4FF]',
        'bright-magenta' => 'hyde-terminal-fg-bright-magenta text-[#E1ACFF]',
        'bright-cyan' => 'hyde-terminal-fg-bright-cyan text-[#A3F7FF]',
        'bright-white' => 'hyde-terminal-fg-bright-white text-[#FFFFFF]',
    ];

    protected const BACKGROUND_COLORS = [
        'black' => 'hyde-terminal-bg-black bg-[#292D3E]',
        'red' => 'hyde-terminal-bg-red bg-[#F07178]',
        'green' => 'hyde-terminal-bg-green bg-[#C3E88D]',
        'yellow' => 'hyde-terminal-bg-yellow bg-[#FFCB6B]',
        'blue' => 'hyde-terminal-bg-blue bg-[#82AAFF]',
        'magenta' => 'hyde-terminal-bg-magenta bg-[#C792EA]',
        'cyan' => 'hyde-terminal-bg-cyan bg-[#89DDFF]',
        'white' => 'hyde-terminal-bg-white bg-[#D0D0D0]',
        'gray' => 'hyde-terminal-bg-gray bg-[#676E95]',
        'bright-red' => 'hyde-terminal-bg-bright-red bg-[#FF8B92]',
        'bright-green' => 'hyde-terminal-bg-bright-green bg-[#DDFFA7]',
        'bright-yellow' => 'hyde-terminal-bg-bright-yellow bg-[#FFE585]',
        'bright-blue' => 'hyde-terminal-bg-bright-blue bg-[#9CC4FF]',
        'bright-magenta' => 'hyde-terminal-bg-bright-magenta bg-[#E1ACFF]',
        'bright-cyan' => 'hyde-terminal-bg-bright-cyan bg-[#A3F7FF]',
        'bright-white' => 'hyde-terminal-bg-bright-white bg-[#FFFFFF]',
    ];

    public function format(string $text): string
    {
        $output = '';
        $stack = [];

        foreach (preg_split(static::TAG_PATTERN, $text, -1, PREG_SPLIT_DELIM_CAPTURE) ?: [] as $part) {
            if (preg_match('/^<([a-z][^<>]*)>$/i', $part, $matches) && ($classes = $this->resolveStyle($matches[1])) !== null) {
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

    /** @param  array<int, string>  $stack */
    protected function closesOpenTag(string $part, array $stack): bool
    {
        return $stack !== [] && ($part === '</>' || $part === '</'.end($stack).'>');
    }

    /** @return string|null The classes to style the tag with, or null when it is not a style tag. */
    protected function resolveStyle(string $tag): ?string
    {
        return static::STYLES[$tag] ?? $this->resolveInlineStyle($tag);
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
            'fg' => static::FOREGROUND_COLORS[$value] ?? null,
            'bg' => static::BACKGROUND_COLORS[$value] ?? null,
            default => null,
        };
    }
}
