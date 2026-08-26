<?php

declare(strict_types=1);

namespace Hyde\Markdown\Extensions\Processing;

use Hyde\Markdown\Extensions\CodeBlockViewModel;
use Hyde\Markdown\Extensions\Nodes\CodeBlock;
use League\CommonMark\Extension\CommonMark\Node\Block\FencedCode;
use League\CommonMark\Node\Node;
use League\CommonMark\Renderer\ChildNodeRendererInterface;
use League\CommonMark\Renderer\NodeRendererInterface;

/** @internal */
class CodeBlockRenderer implements NodeRendererInterface
{
    public function render(Node $node, ChildNodeRendererInterface $childRenderer): string
    {
        CodeBlock::assertInstanceOf($node);

        $fence = $node->firstChild();

        return (new CodeBlockViewModel(
            contents: $childRenderer->renderNodes($node->children()),
            language: $fence instanceof FencedCode ? (($fence->getInfoWords()[0] ?? '') ?: null) : null,
            label: $fence?->data->get(PrepareCodeBlocks::LABEL_KEY, null),
        ))->render();
    }
}
