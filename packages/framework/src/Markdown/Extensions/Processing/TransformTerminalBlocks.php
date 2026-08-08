<?php

declare(strict_types=1);

namespace Hyde\Markdown\Extensions\Processing;

use Hyde\Markdown\Extensions\Concerns\ParsesFenceModifiers;
use Hyde\Markdown\Extensions\Nodes\TerminalBlock;
use Hyde\Markdown\Extensions\TerminalBlockViewModel;
use League\CommonMark\Event\DocumentParsedEvent;
use League\CommonMark\Extension\CommonMark\Node\Block\FencedCode;

use function strtolower;

/** @internal */
class TransformTerminalBlocks
{
    use ParsesFenceModifiers;

    protected const LANGUAGE = 'terminal';

    public static function claims(FencedCode $node): bool
    {
        return strtolower($node->getInfoWords()[0] ?? '') === static::LANGUAGE;
    }

    public function __invoke(DocumentParsedEvent $event): void
    {
        $terminalBlocks = [];

        foreach ($event->getDocument()->iterator() as $node) {
            if ($node instanceof FencedCode && static::claims($node)) {
                $terminalBlocks[] = $node;
            }
        }

        foreach ($terminalBlocks as $node) {
            $node->replaceWith(new TerminalBlock($this->makeViewModel($node)));
        }
    }

    protected function makeViewModel(FencedCode $node): TerminalBlockViewModel
    {
        $tokens = $this->tokenizeModifiers($node->getInfo() ?? '');

        return new TerminalBlockViewModel($node->getLiteral(), $this->parseTitleModifier($tokens, 'terminal block'));
    }
}
