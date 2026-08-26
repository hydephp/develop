<?php

declare(strict_types=1);

namespace Hyde\Markdown\Extensions\Processing;

use Hyde\Facades\Config;
use Hyde\Markdown\Extensions\Concerns\ParsesFenceModifiers;
use Illuminate\Support\HtmlString;
use League\CommonMark\Event\DocumentParsedEvent;
use League\CommonMark\Extension\CommonMark\Node\Block\FencedCode;

/** @internal */
class PrepareCodeBlocks
{
    use ParsesFenceModifiers;

    /** The node data key the renderer reads the label back from. */
    public const LABEL_KEY = 'hyde/code_block_label';

    public function __invoke(DocumentParsedEvent $event): void
    {
        foreach ($event->getDocument()->iterator() as $node) {
            // Terminal blocks have their own fence syntax, which is not ours to read.
            if ($node instanceof FencedCode && ! TransformTerminalBlocks::claims($node)) {
                $this->prepare($node);
            }
        }
    }

    protected function prepare(FencedCode $node): void
    {
        $info = $node->getInfo() ?? '';

        $title = $this->parseTitleModifier($this->tokenizeModifiers($info), 'code block');

        $node->setInfo($this->withoutTitleModifier($info));

        // A terminal block blanks its title bar with an empty title, but a code block has no
        // default label to blank, so an empty one would only ever render an empty element.
        if ($title !== null && $title !== '') {
            $node->data->set(static::LABEL_KEY, $this->formatLabel($title));
        }
    }

    protected function formatLabel(string $label): HtmlString|string
    {
        return Config::getBool('markdown.allow_html', true) ? new HtmlString($label) : $label;
    }
}
