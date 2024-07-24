<?php

/** @noinspection HtmlUnknownTarget */

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature;

use Hyde\Hyde;
use Hyde\Testing\TestCase;
use Hyde\Pages\InMemoryPage;
use Hyde\Support\Models\Route;
use Hyde\Foundation\Facades\Routes;

/**
 * @covers \Hyde\Markdown\Processing\DynamicMarkdownLinkProcessor
 * @covers \Hyde\Framework\Concerns\Internal\SetsUpMarkdownConverter
 *
 * @see \Hyde\Framework\Testing\Feature\Services\Markdown\DynamicMarkdownLinkProcessorTest
 */
class DynamicMarkdownLinksFeatureTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Routes::addRoute(new Route(new InMemoryPage('home')));
        Routes::addRoute(new Route(new InMemoryPage('blog/post')));
    }

    public function testBasicDynamicMarkdownLinks()
    {
        $input = <<<'MARKDOWN'
        [Home](hyde::route('home'))
        [About](hyde::relativeLink('about'))
        ![Logo](hyde::asset('logo.png'))
        
        [Home](hyde::route(home))
        [About](hyde::relativeLink(about))
        ![Logo](hyde::asset(logo.png))
        MARKDOWN;

        $expected = <<<'HTML'
        <p><a href="home.html">Home</a>
        <a href="about">About</a>
        <img src="media/logo.png" alt="Logo" /></p>
        <p><a href="home.html">Home</a>
        <a href="about">About</a>
        <img src="media/logo.png" alt="Logo" /></p>

        HTML;

        $this->assertSame($expected, Hyde::markdown($input)->toHtml());
    }

    public function testDynamicMarkdownLinksWithDoubleQuotes()
    {
        $this->markTestSkipped('https://github.com/hydephp/develop/pull/1590#discussion_r1690082732');

        $input = <<<'MARKDOWN'
        [Home](hyde::route("home"))
        [About](hyde::relativeLink("about"))
        ![Logo](hyde::asset("logo.png"))
        MARKDOWN;

        $expected = <<<'HTML'
        <p><a href="home.html">Home</a>
        <a href="about">About</a>
        <img src="media/logo.png" alt="Logo" /></p>

        HTML;

        $this->assertSame($expected, Hyde::markdown($input)->toHtml());
    }

    public function testDynamicMarkdownLinksInParagraphs()
    {
        $input = <<<'MARKDOWN'
        This is a paragraph with a [link to home](hyde::route('home')) and an [about link](hyde::relativeLink('about')).

        Another paragraph with an ![image](hyde::asset('image.jpg')).
        MARKDOWN;

        $expected = <<<'HTML'
        <p>This is a paragraph with a <a href="home.html">link to home</a> and an <a href="about">about link</a>.</p>
        <p>Another paragraph with an <img src="media/image.jpg" alt="image" />.</p>

        HTML;

        $this->assertSame($expected, Hyde::markdown($input)->toHtml());
    }

    public function testDynamicMarkdownLinksInLists()
    {
        $input = <<<'MARKDOWN'
        - [Home](hyde::route('home'))
        - [About](hyde::relativeLink('about'))
        - ![Logo](hyde::asset('logo.png'))
        MARKDOWN;

        $expected = <<<'HTML'
        <ul>
        <li>
        <a href="home.html">Home</a>
        </li>
        <li>
        <a href="about">About</a>
        </li>
        <li>
        <img src="media/logo.png" alt="Logo" />
        </li>
        </ul>

        HTML;

        $this->assertSame($expected, Hyde::markdown($input)->toHtml());
    }

    public function testDynamicMarkdownLinksWithNestedRoutes()
    {
        $input = <<<'MARKDOWN'
        [Blog Post](hyde::route('blog/post'))
        [Relative Blog](hyde::relativeLink('blog/post'))
        MARKDOWN;

        $expected = <<<'HTML'
        <p><a href="blog/post.html">Blog Post</a>
        <a href="blog/post">Relative Blog</a></p>

        HTML;

        $this->assertSame($expected, Hyde::markdown($input)->toHtml());
    }

    public function testMixOfDynamicAndRegularMarkdownLinks()
    {
        $input = <<<'MARKDOWN'
        [Home](hyde::route('home'))
        [External](https://example.com)
        [About](hyde::relativeLink('about'))
        [Regular](regular-link.html)
        ![Logo](hyde::asset('logo.png'))
        ![External Image](https://example.com/image.jpg)
        MARKDOWN;

        $expected = <<<'HTML'
        <p><a href="home.html">Home</a>
        <a href="https://example.com">External</a>
        <a href="about">About</a>
        <a href="regular-link.html">Regular</a>
        <img src="media/logo.png" alt="Logo" />
        <img src="https://example.com/image.jpg" alt="External Image" /></p>

        HTML;

        $this->assertSame($expected, Hyde::markdown($input)->toHtml());
    }
}
