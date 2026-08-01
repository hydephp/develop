<?php

declare(strict_types=1);

namespace Hyde\Markdown\Extensions\Concerns;

use InvalidArgumentException;

use function array_slice;
use function preg_match_all;
use function sprintf;
use function str_starts_with;
use function strtolower;

use const PREG_SET_ORDER;
use const PREG_UNMATCHED_AS_NULL;

/**
 * Parses the modifiers following the language in a fenced code block info string.
 *
 * @internal
 */
trait ParsesFenceModifiers
{
    /**
     * Matches one info string token: either an HTML-style attribute with a quoted value
     * (which may contain spaces), or a bare space-free word. The surrounding assertions
     * keep a token from being found inside another one, as modifiers are whitespace separated.
     */
    protected const TOKEN_PATTERN = '/(?<!\S)(?:(?<key>[\w-]+)=(?:"(?<double>[^"]*)"|\'(?<single>[^\']*)\')|(?<word>\S+))(?=\s|$)/';

    /**
     * Tokenize the modifiers following the language, which are order-independent.
     *
     * @return array<int, array{key: ?string, double: ?string, single: ?string, word: ?string}>
     */
    protected function tokenizeModifiers(string $info): array
    {
        preg_match_all(static::TOKEN_PATTERN, $info, $matches, PREG_SET_ORDER | PREG_UNMATCHED_AS_NULL);

        return array_slice($matches, 1);
    }

    /**
     * Resolve the title modifier from a tokenized info string.
     *
     * @param  array<int, array{key: ?string, double: ?string, single: ?string, word: ?string}>  $tokens
     * @param  string  $blockName  The block being parsed, used in the error message.
     */
    protected function parseTitleModifier(array $tokens, string $blockName): ?string
    {
        $title = null;

        foreach ($tokens as $token) {
            if ($token['word'] === null) {
                if (strtolower($token['key']) === 'title') {
                    $title = $token['double'] ?? $token['single'];
                }

                continue;
            }

            $this->assertTitleModifierIsNotMalformed($token['word'], $blockName);
        }

        return $title;
    }

    /**
     * A modifier we don't know about may mean something in a future version, so it is ignored.
     * A malformed title, on the other hand, is a typo we should not silently discard.
     */
    protected function assertTitleModifierIsNotMalformed(string $word, string $blockName): void
    {
        $normalized = strtolower($word);

        if ($normalized === 'title' || str_starts_with($normalized, 'title=')) {
            throw new InvalidArgumentException(sprintf(
                'Invalid %s title [%s]. Expected syntax like title="My title".', $blockName, $word
            ));
        }
    }
}
