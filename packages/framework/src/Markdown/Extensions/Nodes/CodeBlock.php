<?php

declare(strict_types=1);

namespace Hyde\Markdown\Extensions\Nodes;

use League\CommonMark\Node\Block\AbstractBlock;

/**
 * Wraps a fenced code block so that Hyde can render the view around it without
 * taking over the rendering of the fence itself, which stays as a child node.
 *
 * @internal
 */
class CodeBlock extends AbstractBlock
{
    //
}
