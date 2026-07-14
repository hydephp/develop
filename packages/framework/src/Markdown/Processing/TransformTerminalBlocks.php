<?php

declare(strict_types=1);

namespace Hyde\Markdown\Processing;

use Hyde\Markdown\Nodes\TerminalBlock;
use League\CommonMark\Event\DocumentParsedEvent;
use League\CommonMark\Extension\CommonMark\Node\Block\FencedCode;

use function array_map;
use function array_slice;
use function in_array;
use function strtolower;

/** @internal */
final class TransformTerminalBlocks
{
    public function __invoke(DocumentParsedEvent $event): void
    {
        $terminalBlocks = [];

        foreach ($event->getDocument()->iterator() as $node) {
            if ($node instanceof FencedCode && strtolower($node->getInfoWords()[0] ?? '') === 'terminal') {
                $terminalBlocks[] = $node;
            }
        }

        foreach ($terminalBlocks as $node) {
            $info = array_map('strtolower', $node->getInfoWords());

            $node->replaceWith(new TerminalBlock(
                $node->getLiteral(),
                in_array('xml', array_slice($info, 1), true),
            ));
        }
    }
}
