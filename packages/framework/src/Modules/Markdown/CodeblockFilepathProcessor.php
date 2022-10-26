<?php

declare(strict_types=1);

namespace Hyde\Framework\Modules\Markdown;

use Hyde\Framework\Contracts\MarkdownPostProcessorContract;
use Hyde\Framework\Contracts\MarkdownPreProcessorContract;

/**
 * Resolves file path comments found in Markdown code blocks into a neat badge shown in the top right corner.
 *
 * @see \Hyde\Framework\Testing\Feature\Services\Markdown\CodeblockFilepathProcessorTest
 */
class CodeblockFilepathProcessor implements MarkdownPreProcessorContract, MarkdownPostProcessorContract
{
    /**
     * Extract lines matching the shortcode pattern and replace them with meta-blocks that will be processed later.
     */
    public static function preprocess(string $markdown): string
    {
        $lines = explode("\n", $markdown);

        foreach ($lines as $index => $line) {
            if (static::lineMatchesPattern($line) && ! str_contains($line, '{"shortcodes": false}')) {
                // Add the meta-block two lines before the pattern, placing it just above the code block.
                // This prevents the meta-block from interfering with other processes.
                $lines[$index - 2] .= sprintf(
                    "\n<!-- HYDE[Filepath]%s -->",
                    trim(str_replace(static::$patterns, '', $line))
                );

                // Remove the original comment lines
                unset($lines[$index]);
                // Only unset the next line if it's empty
                if (trim($lines[$index + 1]) === '') {
                    unset($lines[$index + 1]);
                }
            }
        }

        return implode("\n", $lines);
    }

    /**
     * Process the meta-blocks added by the preprocessor, injecting the filepath badge template into the code block.
     */
    public static function postprocess(string $html): string
    {
        $lines = explode("\n", $html);

        /** @var int $index */
        foreach ($lines as $index => $line) {
            if (str_starts_with($line, '<!-- HYDE[Filepath]')) {
                $path = static::trimHydeDirective($line);
                unset($lines[$index]);
                $codeBlockLine = $index + 1;
                $label = static::resolveTemplate($path);

                $lines[$codeBlockLine] = str_contains($html, static::$torchlightKey)
                ? static::injectLabelToTorchlightCodeLine($label, $lines[$codeBlockLine])
                : static::injectLabelToCodeLine($label, $lines[$codeBlockLine]);
            }
        }

        return implode("\n", $lines);
    }

    protected static array $patterns = [
        '// filepath: ',
        '// Filepath: ',
        '# filepath: ',
        '# Filepath: ',
        '// filepath ',
        '// Filepath ',
        '# filepath ',
        '# Filepath ',
    ];

    protected static string $torchlightKey = '<!-- Syntax highlighted by torchlight.dev -->';

    protected static function lineMatchesPattern(string $line): bool
    {
        foreach (static::$patterns as $pattern) {
            if (str_starts_with($line, $pattern)) {
                return true;
            }
        }

        return false;
    }

    protected static function trimHydeDirective(string $line): string
    {
        return trim(str_replace('-->', '', str_replace(
            '<!-- HYDE[Filepath]',
            '',
            $line
        )));
    }

    protected static function resolveTemplate(string $path): string
    {
        return view('hyde::components.filepath-label', [
            'path' => $path,
        ])->render();
    }

    protected static function injectLabelToTorchlightCodeLine(string $label, string $lines): string
    {
        return str_replace(
            static::$torchlightKey,
            static::$torchlightKey.$label,
            $lines
        );
    }

    protected static function injectLabelToCodeLine(string $label, string $lines): string
    {
        return preg_replace(
            '/<pre><code class="language-(.*?)">/',
            '<pre><code class="language-$1">'.$label,
            $lines
        );
    }
}
