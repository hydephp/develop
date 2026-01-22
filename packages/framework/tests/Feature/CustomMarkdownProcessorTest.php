<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature;

use Hyde\Framework\Services\MarkdownService;
use Hyde\Markdown\Contracts\MarkdownPostProcessorContract;
use Hyde\Markdown\Contracts\MarkdownPreProcessorContract;
use Hyde\Testing\TestCase;

/**
 * Tests for custom Markdown processor registration via config.
 *
 * @see \Hyde\Framework\Concerns\Internal\SetsUpMarkdownConverter
 */
#[\PHPUnit\Framework\Attributes\CoversClass(\Hyde\Framework\Concerns\Internal\SetsUpMarkdownConverter::class)]
class CustomMarkdownProcessorTest extends TestCase
{
    public function testCustomPreProcessorsCanBeRegisteredViaConfig()
    {
        config(['markdown.preprocessors' => [
            CustomTestPreProcessor::class,
        ]]);

        $service = new MarkdownService('Hello World');
        $result = $service->parse();

        $this->assertStringContainsString('[PRE]', $result);
    }

    public function testCustomPostProcessorsCanBeRegisteredViaConfig()
    {
        config(['markdown.postprocessors' => [
            CustomTestPostProcessor::class,
        ]]);

        $service = new MarkdownService('Hello World');
        $result = $service->parse();

        $this->assertStringContainsString('[POST]', $result);
    }

    public function testMultipleCustomPreProcessorsCanBeRegistered()
    {
        config(['markdown.preprocessors' => [
            CustomTestPreProcessor::class,
            CustomTestPreProcessorTwo::class,
        ]]);

        $service = new MarkdownService('Hello World');
        $result = $service->parse();

        $this->assertStringContainsString('[PRE]', $result);
        $this->assertStringContainsString('[PRE2]', $result);
    }

    public function testMultipleCustomPostProcessorsCanBeRegistered()
    {
        config(['markdown.postprocessors' => [
            CustomTestPostProcessor::class,
            CustomTestPostProcessorTwo::class,
        ]]);

        $service = new MarkdownService('Hello World');
        $result = $service->parse();

        $this->assertStringContainsString('[POST]', $result);
        $this->assertStringContainsString('[POST2]', $result);
    }

    public function testCustomPreAndPostProcessorsCanBeUsedTogether()
    {
        config([
            'markdown.preprocessors' => [
                CustomTestPreProcessor::class,
            ],
            'markdown.postprocessors' => [
                CustomTestPostProcessor::class,
            ],
        ]);

        $service = new MarkdownService('Hello World');
        $result = $service->parse();

        $this->assertStringContainsString('[PRE]', $result);
        $this->assertStringContainsString('[POST]', $result);
    }

    public function testEmptyProcessorArraysDoNotCauseErrors()
    {
        config([
            'markdown.preprocessors' => [],
            'markdown.postprocessors' => [],
        ]);

        $service = new MarkdownService('Hello World');
        $result = $service->parse();

        $this->assertSame("<p>Hello World</p>\n", $result);
    }

    public function testCustomPreProcessorsRunBeforeConversion()
    {
        config(['markdown.preprocessors' => [
            MarkdownTransformPreProcessor::class,
        ]]);

        $service = new MarkdownService('# Hello');
        $result = $service->parse();

        // The preprocessor changes "Hello" to "Transformed", which then gets rendered as H1
        $this->assertStringContainsString('Transformed', $result);
        $this->assertStringNotContainsString('Hello', $result);
    }

    public function testCustomPostProcessorsRunAfterConversion()
    {
        config(['markdown.postprocessors' => [
            HtmlWrapperPostProcessor::class,
        ]]);

        $service = new MarkdownService('Hello');
        $result = $service->parse();

        // The postprocessor wraps the HTML in a div
        $this->assertStringStartsWith('<div class="wrapper">', $result);
        $this->assertStringContainsString('</div>', $result);
    }

    public function testProcessorsAreExecutedInOrderOfRegistration()
    {
        config(['markdown.preprocessors' => [
            OrderTrackingFirstPreProcessor::class,
            OrderTrackingSecondPreProcessor::class,
        ]]);

        // Reset the tracking array
        OrderTrackingFirstPreProcessor::$order = [];

        $service = new MarkdownService('Test');
        $service->parse();

        $this->assertSame(['first', 'second'], OrderTrackingFirstPreProcessor::$order);
    }

    public function testCustomProcessorsWorkAlongsideBuiltInProcessors()
    {
        config([
            'markdown.preprocessors' => [CustomTestPreProcessor::class],
            'markdown.postprocessors' => [CustomTestPostProcessor::class],
        ]);

        // Use shortcode syntax which is processed by the built-in ShortcodeProcessor
        $service = new MarkdownService('>info This is an info message');
        $result = $service->parse();

        // Custom processors should have run
        $this->assertStringContainsString('[PRE]', $result);
        $this->assertStringContainsString('[POST]', $result);
    }

    public function testRealWorldUseCaseEmojiShortcodes()
    {
        config(['markdown.preprocessors' => [
            EmojiPreProcessor::class,
        ]]);

        $service = new MarkdownService('Hello :wave: World :heart:');
        $result = $service->parse();

        $this->assertStringNotContainsString(':wave:', $result);
        $this->assertStringNotContainsString(':heart:', $result);
    }

    public function testRealWorldUseCaseCustomContainerSyntax()
    {
        config(['markdown.preprocessors' => [
            ContainerSyntaxPreProcessor::class,
        ]]);

        $service = new MarkdownService(":::note\nThis is a note\n:::");
        $result = $service->parse();

        $this->assertStringContainsString('class="custom-note"', $result);
    }

    public function testRealWorldUseCaseExternalLinkProcessor()
    {
        config(['markdown.postprocessors' => [
            ExternalLinkPostProcessor::class,
        ]]);

        $service = new MarkdownService('[External](https://example.com)');
        $result = $service->parse();

        $this->assertStringContainsString('target="_blank"', $result);
        $this->assertStringContainsString('rel="noopener noreferrer"', $result);
    }
}

/**
 * Test preprocessor that prepends [PRE] to the markdown.
 */
class CustomTestPreProcessor implements MarkdownPreProcessorContract
{
    public static function preprocess(string $markdown): string
    {
        return '[PRE] '.$markdown;
    }
}

/**
 * Test preprocessor that prepends [PRE2] to the markdown.
 */
class CustomTestPreProcessorTwo implements MarkdownPreProcessorContract
{
    public static function preprocess(string $markdown): string
    {
        return '[PRE2] '.$markdown;
    }
}

/**
 * Test postprocessor that appends [POST] to the HTML.
 */
class CustomTestPostProcessor implements MarkdownPostProcessorContract
{
    public static function postprocess(string $html): string
    {
        return $html.'[POST]';
    }
}

/**
 * Test postprocessor that appends [POST2] to the HTML.
 */
class CustomTestPostProcessorTwo implements MarkdownPostProcessorContract
{
    public static function postprocess(string $html): string
    {
        return $html.'[POST2]';
    }
}

/**
 * Test preprocessor that transforms markdown content.
 */
class MarkdownTransformPreProcessor implements MarkdownPreProcessorContract
{
    public static function preprocess(string $markdown): string
    {
        return str_replace('Hello', 'Transformed', $markdown);
    }
}

/**
 * Test postprocessor that wraps HTML in a custom div.
 */
class HtmlWrapperPostProcessor implements MarkdownPostProcessorContract
{
    public static function postprocess(string $html): string
    {
        return '<div class="wrapper">'.trim($html).'</div>';
    }
}

/**
 * Test preprocessor for tracking execution order (first).
 */
class OrderTrackingFirstPreProcessor implements MarkdownPreProcessorContract
{
    /** @var array<string> */
    public static array $order = [];

    public static function preprocess(string $markdown): string
    {
        self::$order[] = 'first';

        return $markdown;
    }
}

/**
 * Test preprocessor for tracking execution order (second).
 */
class OrderTrackingSecondPreProcessor implements MarkdownPreProcessorContract
{
    public static function preprocess(string $markdown): string
    {
        OrderTrackingFirstPreProcessor::$order[] = 'second';

        return $markdown;
    }
}

/**
 * Real-world example: Emoji shortcode processor.
 */
class EmojiPreProcessor implements MarkdownPreProcessorContract
{
    public static function preprocess(string $markdown): string
    {
        $emojis = [
            ':wave:' => "\u{1F44B}",
            ':heart:' => "\u{2764}\u{FE0F}",
            ':smile:' => "\u{1F604}",
        ];

        return str_replace(array_keys($emojis), array_values($emojis), $markdown);
    }
}

/**
 * Real-world example: Container syntax processor (like VuePress).
 */
class ContainerSyntaxPreProcessor implements MarkdownPreProcessorContract
{
    public static function preprocess(string $markdown): string
    {
        return preg_replace(
            '/:::(\w+)\n(.*?)\n:::/s',
            '<div class="custom-$1">$2</div>',
            $markdown
        ) ?? $markdown;
    }
}

/**
 * Real-world example: External link processor.
 */
class ExternalLinkPostProcessor implements MarkdownPostProcessorContract
{
    public static function postprocess(string $html): string
    {
        return preg_replace(
            '/<a href="(https?:\/\/[^"]+)"/',
            '<a href="$1" target="_blank" rel="noopener noreferrer"',
            $html
        ) ?? $html;
    }
}
