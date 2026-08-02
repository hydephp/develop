<?php

declare(strict_types=1);

namespace Hyde\Markdown\Extensions\Processing;

use Hyde\Markdown\Extensions\CodeBlockViewModel;
use Hyde\Markdown\Extensions\Nodes\CodeBlock;
use League\CommonMark\Extension\CommonMark\Node\Block\FencedCode;
use League\CommonMark\Node\Node;
use League\CommonMark\Renderer\ChildNodeRendererInterface;
use League\CommonMark\Renderer\NodeRendererInterface;

use function sprintf;
use function get_class;

use InvalidArgumentException;

/**
 * Renders the view around a code block, leaving the fence inside it to be rendered
 * by whichever renderer the environment already had for it.
 *
 * @internal
 */
class CodeBlockRenderer implements NodeRendererInterface
{
    public function render(Node $node, ChildNodeRendererInterface $childRenderer): string
    {
        if (! $node instanceof CodeBlock || ! $node->firstChild() instanceof FencedCode) {
            throw new InvalidArgumentException(sprintf('Incompatible node type: %s', get_class($node)));
        }

        /** @var \League\CommonMark\Extension\CommonMark\Node\Block\FencedCode $fence */
        $fence = $node->firstChild();

        return (new CodeBlockViewModel(
            contents: $childRenderer->renderNodes([$fence]),
            language: ($fence->getInfoWords()[0] ?? '') ?: null,
            label: $fence->data->get(PrepareCodeBlocks::LABEL_KEY, null),
        ))->render();
    }
}
