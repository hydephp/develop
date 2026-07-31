<?php

declare(strict_types=1);

namespace Hyde\Markdown\Extensions\Processing;

use Hyde\Markdown\Extensions\Nodes\TerminalBlock;
use InvalidArgumentException;
use League\CommonMark\Event\DocumentParsedEvent;
use League\CommonMark\Extension\CommonMark\Node\Block\FencedCode;

use function array_slice;
use function preg_match;
use function sprintf;
use function str_starts_with;
use function strlen;
use function strtolower;

use const PREG_UNMATCHED_AS_NULL;

/** @internal */
class TransformTerminalBlocks
{
    /**
     * Matches the next info string token: either an HTML-style attribute with a quoted value
     * (which may contain spaces), or a bare space-free word. Anchored to the cursor, and
     * required to end at a token boundary, so that tokens must be separated by whitespace.
     */
    protected const TOKEN_PATTERN = '/\G\s*(?:(?<key>[\w-]+)=(?:"(?<double>[^"]*)"|\'(?<single>[^\']*)\')|(?<word>\S+))(?=\s|$)/';

    public function __invoke(DocumentParsedEvent $event): void
    {
        $terminalBlocks = [];

        foreach ($event->getDocument()->iterator() as $node) {
            if ($node instanceof FencedCode && strtolower($node->getInfoWords()[0] ?? '') === 'terminal') {
                $terminalBlocks[] = $node;
            }
        }

        foreach ($terminalBlocks as $node) {
            [$usesSymfonyFormatting, $title] = $this->parseModifiers($node->getInfo() ?? '');

            $node->replaceWith(new TerminalBlock($node->getLiteral(), $usesSymfonyFormatting, $title));
        }
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
        $titled = false;

        // The first token is the language, which is what got us here in the first place.
        foreach (array_slice($this->tokenize($info), 1) as $token) {
            if ($token['word'] !== null) {
                if (strtolower($token['word']) === 'xml') {
                    $usesSymfonyFormatting = true;

                    continue;
                }

                $this->rejectMalformedTitles($token['word']);

                // Any other modifier may mean something in a future version, so it is ignored.
                continue;
            }

            if (strtolower($token['key']) !== 'title') {
                continue;
            }

            if ($titled) {
                throw new InvalidArgumentException('A terminal block can only have one title.');
            }

            $title = $token['double'] ?? $token['single'];
            $titled = true;
        }

        return [$usesSymfonyFormatting, $title];
    }

    /**
     * Split the info string into its tokens, walking a cursor through it so that each
     * token has to end where the next one begins, instead of being found anywhere.
     *
     * @return array<int, array{key: string|null, double: string|null, single: string|null, word: string|null}>
     */
    protected function tokenize(string $info): array
    {
        $tokens = [];
        $cursor = 0;

        // Each match is anchored to the cursor, so the walk ends as soon as a token doesn't start there.
        while (preg_match(static::TOKEN_PATTERN, $info, $matches, PREG_UNMATCHED_AS_NULL, $cursor)) {
            $tokens[] = $matches;
            $cursor += strlen($matches[0]);
        }

        return $tokens;
    }

    /**
     * A modifier we don't know about is ignored, since it may mean something in a future version.
     * A title we can't read, on the other hand, is a typo we should not silently discard.
     */
    protected function rejectMalformedTitles(string $word): void
    {
        $normalized = strtolower($word);

        if ($normalized === 'title' || str_starts_with($normalized, 'title=')) {
            throw new InvalidArgumentException(sprintf(
                'Invalid terminal block title [%s]. Expected a quoted value, like title="My title".', $word
            ));
        }
    }
}
