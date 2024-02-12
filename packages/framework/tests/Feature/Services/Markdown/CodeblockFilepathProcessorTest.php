<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature\Services\Markdown;

use Hyde\Markdown\Processing\CodeblockFilepathProcessor;
use Hyde\Testing\TestCase;

/**
 * @covers \Hyde\Markdown\Processing\CodeblockFilepathProcessor
 */
class CodeblockFilepathProcessorTest extends TestCase
{
    public function testPreprocessExpandsFilepath()
    {
        $markdown = "\n```php\n// filepath: foo.php\necho 'Hello World';\n```";
        $expected = "\n<!-- HYDE[Filepath]foo.php -->\n```php\necho 'Hello World';\n```";

        $this->assertEquals($expected, CodeblockFilepathProcessor::preprocess($markdown));
    }

    public function testPreprocessAcceptsMultipleFilepathFormats()
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

    public function testFilepathPatternIsCaseInsensitive()
    {
        $patterns = [
            '// filepath: ',
            '// Filepath: ',
            '// FilePath: ',
            '// FILEPATH: ',
        ];

        foreach ($patterns as $pattern) {
            $markdown = "\n```php\n{$pattern}foo.php\necho 'Hello World';\n```";
            $expected = "\n<!-- HYDE[Filepath]foo.php -->\n```php\necho 'Hello World';\n```";

            $this->assertEquals($expected, CodeblockFilepathProcessor::preprocess($markdown));
        }
    }

    public function testPreprocessAcceptsMultipleLanguages()
    {
        $languages = [
            'php',
            'js',
            'html',
            'made-up',
            'foo',
        ];

        foreach ($languages as $language) {
            $markdown = "\n```$language\n// filepath: foo.$language\nfoo\n```";
            $expected = "\n<!-- HYDE[Filepath]foo.$language -->\n```$language\nfoo\n```";

            $this->assertEquals($expected, CodeblockFilepathProcessor::preprocess($markdown));
        }

        $this->assertEquals($expected, CodeblockFilepathProcessor::preprocess($markdown));
    }

    public function testPreprocessAcceptsMultipleInputBlocks()
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

        $this->assertSame($expected, CodeblockFilepathProcessor::preprocess($markdown));
    }

    public function testPreprocessAcceptsMultiLineCodeblocks()
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

        $this->assertSame($expected, CodeblockFilepathProcessor::preprocess($markdown));
    }

    public function testSpaceAfterFilepathIsOptional()
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

        $this->assertSame(CodeblockFilepathProcessor::preprocess($expected),
            CodeblockFilepathProcessor::preprocess($markdown));
    }

    public function testProcessorExpandsFilepathDirectiveInStandardCodeblock()
    {
        $html = <<<'HTML'
        <!-- HYDE[Filepath]foo.html -->
        <pre><code class="language-html"></code></pre>
        HTML;

        $expected = <<<'HTML'
        <pre><code class="language-html"><small class="filepath not-prose"><span class="sr-only">Filepath: </span>foo.html</small></code></pre>
        HTML;

        $this->assertSame($expected, CodeblockFilepathProcessor::postprocess($html));
    }

    public function testProcessorExpandsFilepathDirectiveInTorchlightCodeblock()
    {
        $html = <<<'HTML'
        <!-- HYDE[Filepath]foo.html -->
        <pre><code class="torchlight"><!-- Syntax highlighted by torchlight.dev --><div class="line"><span class="line-number">1</span>&nbsp;</div></code></pre>
        HTML;

        $expected = <<<'HTML'
        <pre><code class="torchlight"><!-- Syntax highlighted by torchlight.dev --><small class="filepath not-prose"><span class="sr-only">Filepath: </span>foo.html</small><div class="line"><span class="line-number">1</span>&nbsp;</div></code></pre>
        HTML;

        $this->assertSame($expected, CodeblockFilepathProcessor::postprocess($html));
    }

    public function testProcessorEscapesHtmlByDefault()
    {
        $html = <<<'HTML'
        <!-- HYDE[Filepath]<a href="">Link</a> -->
        <pre><code class="language-html"></code></pre>
        HTML;

        $escaped = e('<a href="">Link</a>');
        $expected = <<<HTML
        <pre><code class="language-html"><small class="filepath not-prose"><span class="sr-only">Filepath: </span>$escaped</small></code></pre>
        HTML;

        $this->assertSame($expected, CodeblockFilepathProcessor::postprocess($html));
    }

    public function testProcessorDoesNotEscapeHtmlIfConfigured()
    {
        config(['markdown.allow_html' => true]);

        $html = <<<'HTML'
        <!-- HYDE[Filepath]<a href="">Link</a> -->
        <pre><code class="language-html"></code></pre>
        HTML;

        $expected = <<<'HTML'
        <pre><code class="language-html"><small class="filepath not-prose"><span class="sr-only">Filepath: </span><a href="">Link</a></small></code></pre>
        HTML;

        $this->assertSame($expected, CodeblockFilepathProcessor::postprocess($html));
    }
}
