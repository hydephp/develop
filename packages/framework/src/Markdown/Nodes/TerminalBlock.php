<?php

declare(strict_types=1);

namespace Hyde\Markdown\Nodes;

use League\CommonMark\Node\Block\AbstractBlock;

/** @internal */
final class TerminalBlock extends AbstractBlock
{
    public function __construct(
        public readonly string $literal,
        public readonly bool $usesSymfonyFormatting = false,
    ) {
        parent::__construct();
    }
}
