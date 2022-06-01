<?php

namespace Hyde\Framework\Services\Markdown;

/**
 * DOMDocument Proof of Concept
 *
 * @todo add ext-dom suggestion to composer.json
 */
class AddFilepathLabelToCodeblockPostProcessor
{
    public static function process(string $html): string
    {
        return (new static($html))->run();
    }

    protected string $html;

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

    public function __construct(string $html) {
        $this->html = $html;

    }

    public function run(): string
    {
        if (! extension_loaded('dom')) {
            return $this->html;
        }

        // Find all the code blocks
        $dom = new \DOMDocument();
        $dom->loadHTML($this->html);
        $xpath = new \DOMXPath($dom);
        // Get query matching <pre><code>
        $query = '//pre/code';
        $codeBlocks = $xpath->query($query);

        // Add the filepath label to each code block
        foreach ($codeBlocks as $codeBlock) {
            // Get the first line (everything before the first newline in $codeBlock->textContent)
            $firstLine = strtok($codeBlock->textContent, "\n");
            
            // Check if it matches any of the patterns
            if ($this->lineMatchesPattern($firstLine)) {
                // Get the filepath
                $filepath = trim(str_replace(self::$patterns, '', $firstLine));

                // Remove the first line of the code block text
                $text = explode("\n", $codeBlock->textContent);
                array_shift($text);
                array_shift($text);
                $codeBlock->textContent = implode("\n", $text);

                // Create the filepath label `<small>$filepath<small>` element
                $filepathLabel = $dom->createElement('small', $filepath);
                // Add the class `filepath` to the filepath label
                $filepathLabel->setAttribute('class', 'filepath');

                // Prepend the filepath label to the first child of the code block
                $codeBlock->insertBefore($filepathLabel, $codeBlock->firstChild);
            }
        }

        return $dom->saveHTML();
    }

    protected function lineMatchesPattern(string $line): bool
    {
        foreach (static::$patterns as $pattern) {
            if (str_starts_with($line, $pattern)) {
                return true;
            }
        }

        return false;
    }
}