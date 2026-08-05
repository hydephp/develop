<?php

declare(strict_types=1);

namespace Hyde\Markdown\Extensions\Processing;

use Hyde\Markdown\Extensions\Nodes\CodeBlock;
use League\CommonMark\Event\DocumentPreRenderEvent;
use League\CommonMark\Extension\CommonMark\Node\Block\FencedCode;

/** @internal */
class WrapCodeBlocks
{
    public function __invoke(DocumentPreRenderEvent $event): void
    {
        $fences = [];

        foreach ($event->getDocument()->iterator() as $node) {
            if ($node instanceof FencedCode && ! $node->parent() instanceof CodeBlock) {
                $fences[] = $node;
            }
        }

        foreach ($fences as $fence) {
            $wrapper = new CodeBlock();

            $fence->replaceWith($wrapper);
            $wrapper->appendChild($fence);
        }
    }
}
