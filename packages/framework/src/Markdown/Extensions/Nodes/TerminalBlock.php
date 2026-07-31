<?php

declare(strict_types=1);

namespace Hyde\Markdown\Extensions\Nodes;

use Hyde\Markdown\Extensions\TerminalBlockViewModel;
use League\CommonMark\Node\Block\AbstractBlock;

/** @internal */
class TerminalBlock extends AbstractBlock
{
    public function __construct(public readonly TerminalBlockViewModel $viewModel)
    {
        parent::__construct();
    }
}
