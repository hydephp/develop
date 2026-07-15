<?php

declare(strict_types=1);

namespace Hyde\Markdown\Extensions;

use Hyde\Markdown\Extensions\Nodes\TerminalBlock;
use Hyde\Markdown\Extensions\Processing\TerminalBlockRenderer;
use Hyde\Markdown\Extensions\Processing\TransformTerminalBlocks;
use League\CommonMark\Environment\EnvironmentBuilderInterface;
use League\CommonMark\Event\DocumentParsedEvent;
use League\CommonMark\Extension\ExtensionInterface;

/** @internal */
class TerminalExtension implements ExtensionInterface
{
    public function register(EnvironmentBuilderInterface $environment): void
    {
        $environment
            ->addEventListener(DocumentParsedEvent::class, new TransformTerminalBlocks(), 100)
            ->addRenderer(TerminalBlock::class, new TerminalBlockRenderer());
    }
}
