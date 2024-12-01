<?php

declare(strict_types=1);

namespace Hyde\Markdown\Processing;

use League\CommonMark\Extension\CommonMark\Node\Block\Heading;
use League\CommonMark\Node\Node;
use League\CommonMark\Renderer\ChildNodeRendererInterface;
use League\CommonMark\Renderer\NodeRendererInterface;

/**
 * Renders a heading node, and supports built-in permalink generation.
 *
 * @see \League\CommonMark\Extension\CommonMark\Renderer\Block\HeadingRenderer
 */
class HeadingRenderer implements NodeRendererInterface
{
    public function render(Node $node, ChildNodeRendererInterface $childRenderer): string
    {
        if (! ($node instanceof Heading)) {
            throw new \InvalidArgumentException('Incompatible node type: '.get_class($node));
        }

        $content = $childRenderer->renderNodes($node->children());

        return view('hyde::components.markdown-heading', [
            'level' => $node->getLevel(),
            'slot' => $content,
            'addPermalink' => config('markdown.permalinks.enabled', true) && ! str_contains($content, 'class="heading-permalink"'),
            'extraAttributes' => $node->data->get('attributes'),
        ])->render();
    }
}
