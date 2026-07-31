<?php

declare(strict_types=1);

namespace Hyde\Markdown\Extensions\Nodes;

use Hyde\Framework\Views\Components\TerminalBlockComponent;
use League\CommonMark\Node\Block\AbstractBlock;

/** @internal */
class TerminalBlock extends AbstractBlock
{
    public function __construct(public readonly TerminalBlockComponent $component)
    {
        parent::__construct();
    }
}
