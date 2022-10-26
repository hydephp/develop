<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature\Services\Markdown;

use Hyde\Framework\Modules\Markdown\CodeblockFilepathProcessor;
use Hyde\Testing\TestCase;

/**
 * @covers \Hyde\Framework\Modules\Markdown\CodeblockFilepathProcessor
 */
class CodeblockFilepathProcessorTest extends TestCase
{
    public function test_preprocess_expands_filepath()
    {
        $markdown = "\n```php\n// filepath: foo.php\necho 'Hello World';\n```";
        $expected = "\n<!-- HYDE[Filepath]foo.php -->\n```php\necho 'Hello World';\n```";

        $this->assertEquals($expected, CodeblockFilepathProcessor::preprocess($markdown));
    }

    public function test_preprocess_accepts_multiple_filepath_formats()
    {
        $patterns = [
            '// filepath: ',
            '// Filepath: ',
            '# filepath: ',
            '# Filepath: ',
            '// filepath ',
            '// Filepath ',
            '# filepath ',
            '# Filepath ',
        ];

        foreach ($patterns as $pattern) {
            $markdown = "\n```php\n{$pattern}foo.php\necho 'Hello World';\n```";
            $expected = "\n<!-- HYDE[Filepath]foo.php -->\n```php\necho 'Hello World';\n```";

            $this->assertEquals($expected, CodeblockFilepathProcessor::preprocess($markdown));
        }
    }

    public function test_preprocess_accepts_multiple_languages()
    {
        $languages = [
            'php',
            'js',
            'html',
            'made-up',
            'foo',
        ];

        foreach ($languages as $language) {
            $markdown = "\n```{$language}\n// filepath: foo.{$language}\nfoo\n```";
            $expected = "\n<!-- HYDE[Filepath]foo.{$language} -->\n```{$language}\nfoo\n```";

            $this->assertEquals($expected, \Hyde\Framework\Modules\Markdown\CodeblockFilepathProcessor::preprocess($markdown));
        }

        $this->assertEquals($expected, \Hyde\Framework\Modules\Markdown\CodeblockFilepathProcessor::preprocess($markdown));
    }

    public function test_preprocess_accepts_multiple_input_blocks()
    {
        $markdown = <<<'MD'
        
        ```php
        // filepath: foo.php
        echo 'Hello World';
        ```
        
        ```js
        // filepath: bar.js
        echo 'Hello World';
        ```
        MD;

        $expected = <<<'MD'
        
        <!-- HYDE[Filepath]foo.php -->
        ```php
        echo 'Hello World';
        ```
        
        <!-- HYDE[Filepath]bar.js -->
        ```js
        echo 'Hello World';
        ```
        MD;

        $this->assertEqualsIgnoringLineReturnType($expected, CodeblockFilepathProcessor::preprocess($markdown));
    }

    public function test_preprocess_accepts_multi_line_codeblocks()
    {
        $markdown = <<<'MD'
        
        ```php
        // filepath: foo.php
        echo 'Hello World';
        
        echo 'Hello World';
        ```
        MD;

        $expected = <<<'MD'
        
        <!-- HYDE[Filepath]foo.php -->
        ```php
        echo 'Hello World';
        
        echo 'Hello World';
        ```
        MD;

        $this->assertEqualsIgnoringLineReturnType($expected, \Hyde\Framework\Modules\Markdown\CodeblockFilepathProcessor::preprocess($markdown));
    }

    public function test_space_after_filepath_is_optional()
    {
        $markdown = <<<'MD'
        
        ```php
        // filepath: foo.php
        
        echo 'Hello World';
        ```
        MD;

        $expected = <<<'MD'
        
        ```php
        // filepath: foo.php
        echo 'Hello World';
        ```
        MD;

        $this->assertEqualsIgnoringLineReturnType(\Hyde\Framework\Modules\Markdown\CodeblockFilepathProcessor::preprocess($expected),
            \Hyde\Framework\Modules\Markdown\CodeblockFilepathProcessor::preprocess($markdown));
    }

    public function test_processor_expands_filepath_directive_in_standard_codeblock()
    {
        $html = <<<'HTML'
        <!-- HYDE[Filepath]foo.html -->
        <pre><code class="language-html"></code></pre>
        HTML;

        $expected = <<<'HTML'
        <pre><code class="language-html"><small class="filepath"><span class="sr-only">Filepath: </span>foo.html</small></code></pre>
        HTML;

        $this->assertEqualsIgnoringLineReturnType($expected, CodeblockFilepathProcessor::postprocess($html));
    }

    public function test_processor_expands_filepath_directive_in_torchlight_codeblock()
    {
        $html = <<<'HTML'
        <!-- HYDE[Filepath]foo.html -->
        <pre><code class="torchlight"><!-- Syntax highlighted by torchlight.dev --><div class="line"><span class="line-number">1</span>&nbsp;</div></code></pre>
        HTML;

        $expected = <<<'HTML'
        <pre><code class="torchlight"><!-- Syntax highlighted by torchlight.dev --><small class="filepath"><span class="sr-only">Filepath: </span>foo.html</small><div class="line"><span class="line-number">1</span>&nbsp;</div></code></pre>
        HTML;

        $this->assertEqualsIgnoringLineReturnType($expected, CodeblockFilepathProcessor::postprocess($html));
    }

    protected function assertEqualsIgnoringLineReturnType(string $expected, string $actual)
    {
        $this->assertEquals(str_replace("\r\n", "\n", $expected),
            str_replace("\r\n", "\n", $actual));
    }
}
