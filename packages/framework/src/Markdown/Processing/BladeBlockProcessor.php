<?php

declare(strict_types=1);

namespace Hyde\Markdown\Processing;

use Hyde\Markdown\Contracts\MarkdownPostProcessorContract;
use Hyde\Markdown\Contracts\MarkdownPreProcessorContract;
use Hyde\Markdown\Processing\BladeBlocks\BladeBlock;
use Hyde\Markdown\Processing\BladeBlocks\BladeBlockExtractor;

use function array_map;
use function strtr;

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
        $blocks = static::$blocks;
        static::$blocks = [];

        return strtr($html, array_map(
            fn (BladeBlock $block): string => $block->compile(),
            $blocks,
        ));
    }
}
