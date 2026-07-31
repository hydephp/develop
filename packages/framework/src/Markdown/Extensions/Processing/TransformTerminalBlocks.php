<?php

declare(strict_types=1);

namespace Hyde\Markdown\Extensions\Processing;

use Hyde\Framework\Views\Components\TerminalBlockComponent;
use Hyde\Markdown\Extensions\Nodes\TerminalBlock;
use InvalidArgumentException;
use League\CommonMark\Event\DocumentParsedEvent;
use League\CommonMark\Extension\CommonMark\Node\Block\FencedCode;

use function array_slice;
use function preg_match_all;
use function sprintf;
use function str_starts_with;
use function strtolower;

use const PREG_SET_ORDER;
use const PREG_UNMATCHED_AS_NULL;

/** @internal */
class TransformTerminalBlocks
{
    /**
     * Matches one info string token: either an HTML-style attribute with a quoted value
     * (which may contain spaces), or a bare space-free word. The surrounding assertions
     * keep a token from being found inside another one, as modifiers are whitespace separated.
     */
    protected const TOKEN_PATTERN = '/(?<!\S)(?:(?<key>[\w-]+)=(?:"(?<double>[^"]*)"|\'(?<single>[^\']*)\')|(?<word>\S+))(?=\s|$)/';

    public function __invoke(DocumentParsedEvent $event): void
    {
        $terminalBlocks = [];

        foreach ($event->getDocument()->iterator() as $node) {
            if ($node instanceof FencedCode && strtolower($node->getInfoWords()[0] ?? '') === 'terminal') {
                $terminalBlocks[] = $node;
            }
        }

        foreach ($terminalBlocks as $node) {
            $node->replaceWith(new TerminalBlock($this->makeComponent($node)));
        }
    }

    protected function makeComponent(FencedCode $node): TerminalBlockComponent
    {
        [$usesSymfonyFormatting, $title] = $this->parseModifiers($node->getInfo() ?? '');

        return new TerminalBlockComponent($node->getLiteral(), $title, $usesSymfonyFormatting);
    }

    /**
     * Parse the modifiers following the language, which are order-independent.
     *
     * @return array{0: bool, 1: string|null} Whether Symfony formatting is used, and the window title.
     */
    protected function parseModifiers(string $info): array
    {
        $usesSymfonyFormatting = false;
        $title = null;

        preg_match_all(static::TOKEN_PATTERN, $info, $matches, PREG_SET_ORDER | PREG_UNMATCHED_AS_NULL);

        foreach (array_slice($matches, 1) as $token) {
            if ($token['word'] === null) {
                if (strtolower($token['key']) === 'title') {
                    $title = $token['double'] ?? $token['single'];
                }

                continue;
            }

            $word = strtolower($token['word']);

            if ($word === 'xml') {
                $usesSymfonyFormatting = true;

                continue;
            }

            // A modifier we don't know about may mean something in a future version, so it is ignored.
            // A malformed title, on the other hand, is a typo we should not silently discard.
            if ($word === 'title' || str_starts_with($word, 'title=')) {
                throw new InvalidArgumentException(sprintf(
                    'Invalid terminal block title [%s]. Expected syntax like title="My title".', $token['word']
                ));
            }
        }

        return [$usesSymfonyFormatting, $title];
    }
}
