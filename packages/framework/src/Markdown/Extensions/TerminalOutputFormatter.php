<?php

declare(strict_types=1);

namespace Hyde\Markdown\Extensions;

use function array_pop;
use function count;
use function e;
use function end;
use function preg_match;
use function preg_split;
use function str_repeat;

/**
 * Renders the console formatter tags of a terminal block line as styled markup.
 *
 * @internal
 */
class TerminalOutputFormatter
{
    protected const TAG_PATTERN = '/(<\/?[a-z][^<>]*>)/i';

    protected const STYLES = [
        'info' => 'hyde-terminal-info text-[#C3E88D]',
        'comment' => 'hyde-terminal-comment text-[#FFCB6B]',
        'question' => 'hyde-terminal-question text-[#89DDFF]',
        'error' => 'hyde-terminal-error font-semibold text-[#F07178]',
    ];

    public function format(string $text): string
    {
        $output = '';
        $stack = [];

        foreach (preg_split(static::TAG_PATTERN, $text, -1, PREG_SPLIT_DELIM_CAPTURE) ?: [] as $part) {
            if (preg_match('/^<([a-z][^<>]*)>$/i', $part, $matches) && isset(static::STYLES[$matches[1]])) {
                $stack[] = $matches[1];
                $output .= '<span class="'.static::STYLES[$matches[1]].'">';
            } elseif (preg_match('/^<\/([a-z][^<>]*)>$/i', $part, $matches) && end($stack) === $matches[1]) {
                array_pop($stack);
                $output .= '</span>';
            } else {
                $output .= e($part);
            }
        }

        return $output.str_repeat('</span>', count($stack));
    }
}
