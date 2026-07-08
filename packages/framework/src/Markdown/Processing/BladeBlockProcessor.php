<?php

declare(strict_types=1);

namespace Hyde\Markdown\Processing;

use Hyde\Markdown\Contracts\MarkdownPostProcessorContract;
use Hyde\Markdown\Contracts\MarkdownPreProcessorContract;
use Hyde\Markdown\Processing\BladeBlocks\BladeBlockExtractor;

use function str_contains;
use function str_replace;

/**
 * Renders executable Blade code blocks within any Markdown page.
 *
 * A sister feature to the {@see BladeDownProcessor}. The preprocessor extracts each block
 * into an object and leaves an HTML comment signature; the postprocessor swaps each
 * signature for the compiled block.
 *
 * @see \Hyde\Markdown\Processing\BladeBlocks\BladeBlock
 */
class BladeBlockProcessor implements MarkdownPreProcessorContract, MarkdownPostProcessorContract
{
    /**
     * The extracted blocks, keyed by their signature.
     *
     * @var array<string, \Hyde\Markdown\Processing\BladeBlocks\BladeBlock>
     */
    protected static array $blocks = [];

    public static function preprocess(string $markdown): string
    {
        [$blocks, $markdown] = (new BladeBlockExtractor())->handle($markdown);

        static::$blocks += $blocks;

        return $markdown;
    }

    public static function postprocess(string $html): string
    {
        // Compiling a block can re-enter this processor, so we only touch signatures
        // present in this HTML and remove each before compiling it.
        foreach (static::$blocks as $signature => $block) {
            if (str_contains($html, $signature)) {
                unset(static::$blocks[$signature]);

                $html = str_replace($signature, $block->compile(), $html);
            }
        }

        return $html;
    }
}
