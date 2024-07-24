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

    public function testDynamicMarkdownLinks()
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
}
