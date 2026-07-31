<?php

declare(strict_types=1);

namespace Hyde\Markdown\Extensions\Processing;

use Hyde\Markdown\Extensions\Nodes\TerminalBlock;
use InvalidArgumentException;
use League\CommonMark\Node\Node;
use League\CommonMark\Renderer\ChildNodeRendererInterface;
use League\CommonMark\Renderer\NodeRendererInterface;

use function get_class;
use function sprintf;

/** @internal */
class TerminalBlockRenderer implements NodeRendererInterface
{
    public function render(Node $node, ChildNodeRendererInterface $childRenderer): string
    {
        if (! $node instanceof TerminalBlock) {
            throw new InvalidArgumentException(sprintf('Incompatible node type: %s', get_class($node)));
        }

        return $node->viewModel->render();
    }
}
