<?php

declare(strict_types=1);

namespace Hyde\Markdown\Extensions\Concerns;

use InvalidArgumentException;

use function preg_match_all;
use function str_starts_with;
use function array_reverse;
use function array_slice;
use function strtolower;
use function sprintf;
use function strlen;
use function substr;
use function ltrim;
use function rtrim;

use const PREG_OFFSET_CAPTURE;
use const PREG_UNMATCHED_AS_NULL;
use const PREG_SET_ORDER;

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

    /** Stands in for the language of a fence that labelled itself with a title instead of declaring one. */
    protected const FALLBACK_LANGUAGE = 'plaintext';

    /**
     * Tokenize the modifiers following the language, which are order-independent.
     *
     * @return array<int, array{key: ?string, double: ?string, single: ?string, word: ?string}>
     */
    protected function tokenizeModifiers(string $info): array
    {
        preg_match_all(static::TOKEN_PATTERN, $info, $matches, PREG_SET_ORDER | PREG_UNMATCHED_AS_NULL);

        return $this->declaresLanguage($matches[0]['word'] ?? null) ? array_slice($matches, 1) : $matches;
    }

    /**
     * The first token is the language, which is not a modifier. A fence may open with a modifier
     * instead, though, in which case it declares no language and every token is one.
     *
     * @param  ?string  $word  The bare word the info string opens with, if it opens with one at all.
     */
    protected function declaresLanguage(?string $word): bool
    {
        return $word !== null && ! $this->looksLikeTitleModifier($word);
    }

    /**
     * Take the title modifier out of an info string, leaving every other byte of it as it was,
     * since the modifiers we don't know about belong to whichever extension does. The only
     * addition is the fallback language, when the title was standing in the language's place.
     *
     * Only whole tokens are taken, so a `title=` written inside another modifier's quoted value
     * is left alone, being that modifier's business rather than a second title.
     */
    protected function withoutTitleModifier(string $info): string
    {
        preg_match_all(static::TOKEN_PATTERN, $info, $matches, PREG_SET_ORDER | PREG_UNMATCHED_AS_NULL | PREG_OFFSET_CAPTURE);

        $prepared = $info;

        foreach (array_reverse($matches) as $token) {
            if ($token['key'][0] !== null && strtolower($token['key'][0]) === 'title') {
                $prepared = $this->spliceToken($prepared, $token[0][1], strlen($token[0][0]));
            }
        }

        if ($prepared === $info) {
            return $info;
        }

        return $this->declaresLanguage($matches[0]['word'][0] ?? null)
            ? $prepared
            : $this->withFallbackLanguage($prepared);
    }

    /**
     * The first word of an info string is where the language goes, so whatever a title leaves behind
     * on a fence that declared none would be read as one, by us and by whichever highlighter reads
     * the fence next. Naming the language the block actually is keeps them out of that slot.
     */
    protected function withFallbackLanguage(string $info): string
    {
        return rtrim(static::FALLBACK_LANGUAGE.' '.$info);
    }

    protected function spliceToken(string $info, int $offset, int $length): string
    {
        $before = substr($info, 0, $offset);
        $after = substr($info, $offset + $length);

        return $after === '' ? rtrim($before) : $before.ltrim($after);
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
        if ($this->looksLikeTitleModifier($word)) {
            throw new InvalidArgumentException(sprintf(
                'Invalid %s title [%s]. Expected syntax like title="My title".', $blockName, $word
            ));
        }
    }

    protected function looksLikeTitleModifier(string $word): bool
    {
        $normalized = strtolower($word);

        return $normalized === 'title' || str_starts_with($normalized, 'title=');
    }
}
