<?php

declare(strict_types=1);

namespace Hyde\Framework\Views\Components;

use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Contracts\View\View as ViewContract;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\View;
use Illuminate\Support\HtmlString;
use Illuminate\View\Component;

use function array_map;
use function array_pop;
use function count;
use function e;
use function end;
use function explode;
use function implode;
use function preg_match;
use function preg_split;
use function sprintf;
use function str_repeat;

/**
 * A terminal window, rendered through the publishable terminal view.
 *
 * The public properties make up the data contract of that view. Blocks written in Markdown are parsed
 * into instances of this class, which can also be constructed to render a window from anywhere else.
 */
class TerminalBlockComponent extends Component implements Htmlable
{
    /** The terminal output as finished HTML, escaped and marked up for display. */
    public readonly HtmlString $contents;

    /** Blade would otherwise hand these to the view, where they are not part of the contract. */
    protected $except = ['toHtml', 'componentName', 'ignoredParameterNames'];

    public function __construct(
        public readonly string $literal,
        public readonly ?string $title = null,
        public readonly bool $usesSymfonyFormatting = false,
    ) {
        $this->contents = new HtmlString($this->formatContents());
    }

    /**
     * Render the terminal window to HTML using the terminal view.
     */
    public function toHtml(): string
    {
        return Blade::renderComponent($this);
    }

    /** @inheritDoc */
    public function render(): ViewContract
    {
        return View::make('hyde::components.markdown.terminal');
    }

    protected function formatContents(): string
    {
        return implode("\n", array_map(
            fn (string $line): string => $this->formatLine($line),
            explode("\n", $this->literal),
        ));
    }

    protected function formatLine(string $line): string
    {
        if (preg_match('/^(\$[\t ]+)(.*)$/', $line, $matches)) {
            return sprintf(
                '<span class="hyde-terminal-command text-[#C3E88D]"><span class="hyde-terminal-prompt select-none" aria-hidden="true">%s</span>%s</span>',
                e($matches[1]),
                $this->formatText($matches[2]),
            );
        }

        return $this->formatText($line);
    }

    protected function formatText(string $text): string
    {
        if (! $this->usesSymfonyFormatting) {
            return e($text);
        }

        $output = '';
        $stack = [];
        $parts = preg_split('/(<\/?(?:info|comment|question|error)>)/', $text, -1, PREG_SPLIT_DELIM_CAPTURE);

        foreach ($parts ?: [] as $part) {
            if (preg_match('/^<(info|comment|question|error)>$/', $part, $matches)) {
                $stack[] = $matches[1];
                $output .= match ($matches[1]) {
                    'info' => '<span class="hyde-terminal-info text-[#C3E88D]">',
                    'comment' => '<span class="hyde-terminal-comment text-[#FFCB6B]">',
                    'question' => '<span class="hyde-terminal-question text-[#89DDFF]">',
                    'error' => '<span class="hyde-terminal-error font-semibold text-[#F07178]">',
                };
            } elseif (preg_match('/^<\/(info|comment|question|error)>$/', $part, $matches)
                && end($stack) === $matches[1]) {
                array_pop($stack);
                $output .= '</span>';
            } else {
                $output .= e($part);
            }
        }

        return $output.str_repeat('</span>', count($stack));
    }
}
