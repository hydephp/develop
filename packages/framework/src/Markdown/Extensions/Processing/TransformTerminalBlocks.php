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
        [$usesSymfonyFormatting, $title] = $this->parseModifiers($node->getInfo() ?? '');

        return new TerminalBlockViewModel($node->getLiteral(), $title, $usesSymfonyFormatting);
    }

    /**
     * Parse the modifiers following the language, which are order-independent.
     *
     * @return array{0: bool, 1: string|null} Whether Symfony formatting is used, and the window title.
     */
    protected function parseModifiers(string $info): array
    {
        $tokens = $this->tokenizeModifiers($info);

        return [$this->usesSymfonyFormatting($tokens), $this->parseTitleModifier($tokens, 'terminal block')];
    }

    /** @param array<int, array{key: ?string, double: ?string, single: ?string, word: ?string}> $tokens */
    protected function usesSymfonyFormatting(array $tokens): bool
    {
        foreach ($tokens as $token) {
            if ($token['word'] !== null && strtolower($token['word']) === 'xml') {
                return true;
            }
        }

        return false;
    }
}
