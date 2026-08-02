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
     * Matches an HTML-style attribute with a quoted value, which may contain spaces, or a bare
     * space-free word. The surrounding assertions keep a token from being found inside another
     * one, as modifiers are whitespace separated.
     */
    protected const TOKEN_PATTERN = '/(?<!\S)(?:(?<key>[\w-]+)=(?:"(?<double>[^"]*)"|\'(?<single>[^\']*)\')|(?<word>\S+))(?=\s|$)/';

    protected const FALLBACK_LANGUAGE = 'plaintext';

    /**
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
     */
    protected function declaresLanguage(?string $word): bool
    {
        return $word !== null && ! $this->looksLikeTitleModifier($word);
    }

    /**
     * Every other byte of the info string is left as it was, since the modifiers we don't know
     * about belong to whichever extension does. Only whole tokens are taken, so a `title=`
     * written inside another modifier's quoted value stays that modifier's business.
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
     * the fence next.
     */
    protected function withFallbackLanguage(string $info): string
    {
        return rtrim(static::FALLBACK_LANGUAGE . ' ' . $info);
    }

    protected function spliceToken(string $info, int $offset, int $length): string
    {
        $before = substr($info, 0, $offset);
        $after = substr($info, $offset + $length);

        return $after === '' ? rtrim($before) : $before . ltrim($after);
    }

    /**
     * @param  array<int, array{key: ?string, double: ?string, single: ?string, word: ?string}>  $tokens
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
                'Invalid %s title [%s]. Expected syntax like title="My title".',
                $blockName,
                $word
            ));
        }
    }

    protected function looksLikeTitleModifier(string $word): bool
    {
        $normalized = strtolower($word);

        return $normalized === 'title' || str_starts_with($normalized, 'title=');
    }
}
