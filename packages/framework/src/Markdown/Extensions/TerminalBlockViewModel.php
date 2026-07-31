<?php

declare(strict_types=1);

namespace Hyde\Markdown\Extensions;

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
use function view;

/** @internal */
class TerminalBlockViewModel
{
    public readonly string $contents;

    public function __construct(
        public readonly string $literal,
        public readonly ?string $title = null,
        public readonly bool $usesSymfonyFormatting = false,
    ) {
        $this->contents = $this->formatContents();
    }

    public function render(): string
    {
        return view('hyde::components.markdown.terminal', $this->viewData())->render();
    }

    /** @return array{contents: string, title: ?string} */
    protected function viewData(): array
    {
        return [
            'contents' => $this->contents,
            'title' => $this->title,
        ];
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
