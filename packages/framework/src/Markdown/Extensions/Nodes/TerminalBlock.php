<?php

declare(strict_types=1);

namespace Hyde\Markdown\Extensions\Nodes;

use League\CommonMark\Node\Block\AbstractBlock;

/** @internal */
class TerminalBlock extends AbstractBlock
{
    public function __construct(
        public readonly string $literal,
        public readonly bool $usesSymfonyFormatting = false,
        public readonly ?string $title = null,
    ) {
        parent::__construct();
    }
}
