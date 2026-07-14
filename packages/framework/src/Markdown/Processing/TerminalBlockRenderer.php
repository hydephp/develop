<?php

declare(strict_types=1);

namespace Hyde\Markdown\Processing;

use Hyde\Markdown\Nodes\TerminalBlock;
use InvalidArgumentException;
use League\CommonMark\Node\Node;
use League\CommonMark\Renderer\ChildNodeRendererInterface;
use League\CommonMark\Renderer\NodeRendererInterface;

use function array_fill;
use function array_map;
use function array_pop;
use function count;
use function end;
use function e;
use function explode;
use function get_class;
use function implode;
use function preg_match;
use function preg_split;
use function sprintf;
use function view;

/** @internal */
final class TerminalBlockRenderer implements NodeRendererInterface
{
    public function render(Node $node, ChildNodeRendererInterface $childRenderer): string
    {
        if (! $node instanceof TerminalBlock) {
            throw new InvalidArgumentException(sprintf('Incompatible node type: %s', get_class($node)));
        }

        return view('hyde::components.terminal', [
            'contents' => $this->renderContents($node),
        ])->render();
    }

    private function renderContents(TerminalBlock $node): string
    {
        return implode("\n", array_map(
            fn (string $line): string => $this->renderLine($line, $node->usesSymfonyFormatting),
            explode("\n", $node->literal),
        ));
    }

    private function renderLine(string $line, bool $usesSymfonyFormatting): string
    {
        if (preg_match('/^(\$[\t ]+)(.*)$/', $line, $matches)) {
            return sprintf(
                '<span class="hyde-terminal-command text-[#C3E88D]"><span class="hyde-terminal-prompt select-none" aria-hidden="true">%s</span>%s</span>',
                e($matches[1]),
                $this->renderText($matches[2], $usesSymfonyFormatting),
            );
        }

        return $this->renderText($line, $usesSymfonyFormatting);
    }

    private function renderText(string $text, bool $usesSymfonyFormatting): string
    {
        if (! $usesSymfonyFormatting) {
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
                    'question' => '<span class="hyde-terminal-question bg-[#89DDFF] text-black">',
                    'error' => '<span class="hyde-terminal-error bg-[#F07178] text-white">',
                };
            } elseif (preg_match('/^<\/(info|comment|question|error)>$/', $part, $matches)
                && end($stack) === $matches[1]) {
                array_pop($stack);
                $output .= '</span>';
            } else {
                $output .= e($part);
            }
        }

        return $output.implode('', array_fill(0, count($stack), '</span>'));
    }
}
