<?php

namespace Hyde\Framework\Modules\Markdown;

use Hyde\Framework\Contracts\MarkdownPostProcessorContract;
use Hyde\Framework\Contracts\MarkdownPreProcessorContract;

/**
 * @see \Hyde\Framework\Testing\Feature\Services\Markdown\CodeblockFilepathProcessorTest
 */
class CodeblockFilepathProcessor implements MarkdownPreProcessorContract, MarkdownPostProcessorContract
{
    public static function preprocess(string $markdownInput): string
    {
        $lines = explode("\n", $markdownInput);

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

    public static function postprocess(string $htmlInput): string
    {
        $lines = explode("\n", $htmlInput);

        /** @var int $index */
        foreach ($lines as $index => $line) {
            if (str_starts_with($line, '<!-- HYDE[Filepath]')) {
                $path = static::trimHydeDirective($line);
                unset($lines[$index]);
                $codeBlockLine = $index + 1;
                $label = static::resolveTemplate($path);

                $lines[$codeBlockLine] = str_contains($htmlInput, static::$torchlightKey)
                ? static::resolveTorchlightCodeLine($label, $lines[$codeBlockLine])
                : static::resolveCodeLine($label, $lines[$codeBlockLine]);
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
        return sprintf('<small class="filepath"><span class="sr-only">Filepath: </span>%s</small>', $path);
    }

    protected static function resolveTorchlightCodeLine(string $label, string $lines): string
    {
        return str_replace(
            static::$torchlightKey,
            static::$torchlightKey.$label,
            $lines
        );
    }

    protected static function resolveCodeLine(string $label, string $lines): string
    {
        return preg_replace(
            '/<pre><code class="language-(.*?)">/',
            '<pre><code class="language-$1">'.$label,
            $lines
        );
    }
}
