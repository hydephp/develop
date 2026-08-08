<?php

declare(strict_types=1);

namespace Hyde\Markdown\Extensions;

use function array_map;
use function e;
use function explode;
use function implode;
use function preg_match;
use function sprintf;
use function view;

/** @internal */
class TerminalBlockViewModel
{
    public readonly string $contents;

    public function __construct(
        public readonly string $literal,
        public readonly ?string $title = null,
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
                '<span class="hyde-terminal-command"><span class="hyde-terminal-prompt" aria-hidden="true">%s</span>%s</span>',
                e($matches[1]),
                $this->formatText($matches[2]),
            );
        }

        return $this->formatText($line);
    }

    protected function formatText(string $text): string
    {
        return (new TerminalOutputFormatter())->format($text);
    }
}
