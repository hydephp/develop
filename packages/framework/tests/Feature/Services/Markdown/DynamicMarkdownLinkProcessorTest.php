<?php

/** @noinspection HtmlUnknownTarget */

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature\Services\Markdown;

use Hyde\Pages\InMemoryPage;
use Hyde\Testing\UnitTestCase;
use Hyde\Support\Models\Route;
use Hyde\Support\Facades\Render;
use Hyde\Foundation\Facades\Routes;
use Hyde\Support\Models\RenderData;
use Hyde\Markdown\Processing\DynamicMarkdownLinkProcessor;

/**
 * @covers \Hyde\Markdown\Processing\DynamicMarkdownLinkProcessor
 *
 * @see \Hyde\Framework\Testing\Feature\DynamicMarkdownLinksFeatureTest
 */
class DynamicMarkdownLinkProcessorTest extends UnitTestCase
{
    protected static bool $needsConfig = true;
    protected static bool $needsKernel = true;

    protected function setUp(): void
    {
        parent::setUp();

        Render::swap(new RenderData());

        Routes::addRoute(new Route(new InMemoryPage('home')));
    }

    public function testRouteReplacement()
    {
        $input = '<p><a href="hyde::route(\'home\')">Home</a></p>';
        $expected = '<p><a href="home.html">Home</a></p>';

        $this->assertSame($expected, DynamicMarkdownLinkProcessor::postprocess($input));
    }

    public function testRouteReplacementWithoutQuotes()
    {
        $input = '<p><a href="hyde::route(home)">Home</a></p>';
        $expected = '<p><a href="home.html">Home</a></p>';

        $this->assertSame($expected, DynamicMarkdownLinkProcessor::postprocess($input));
    }

    public function testRelativeLinkReplacement()
    {
        $input = '<p><a href="hyde::relativeLink(\'about\')">About</a></p>';
        $expected = '<p><a href="about">About</a></p>';

        $this->assertSame($expected, DynamicMarkdownLinkProcessor::postprocess($input));
    }

    public function testRelativeLinkReplacementWithoutQuotes()
    {
        $input = '<p><a href="hyde::relativeLink(about)">About</a></p>';
        $expected = '<p><a href="about">About</a></p>';

        $this->assertSame($expected, DynamicMarkdownLinkProcessor::postprocess($input));
    }

    public function testAssetReplacement()
    {
        $input = '<p><img src="hyde::asset(\'image.jpg\')" alt="Image" /></p>';
        $expected = '<p><img src="media/image.jpg" alt="Image" /></p>';

        $this->assertSame($expected, DynamicMarkdownLinkProcessor::postprocess($input));
    }

    public function testAssetReplacementWithoutQuotes()
    {
        $input = '<p><img src="hyde::asset(image.jpg)" alt="Image" /></p>';
        $expected = '<p><img src="media/image.jpg" alt="Image" /></p>';

        $this->assertSame($expected, DynamicMarkdownLinkProcessor::postprocess($input));
    }

    public function testMultipleReplacements()
    {
        $input = <<<'HTML'
        <a href="hyde::route('home')">Home</a>
        <a href="hyde::relativeLink('about')">About</a>
        <img src="hyde::asset('logo.png')" alt="Logo" />
        HTML;

        $expected = <<<'HTML'
        <a href="home.html">Home</a>
        <a href="about">About</a>
        <img src="media/logo.png" alt="Logo" />
        HTML;

        $this->assertSame($expected, DynamicMarkdownLinkProcessor::postprocess($input));
    }

    public function testNoReplacements()
    {
        $input = '<p>This is a regular <a href="https://example.com">link</a> with no Hyde syntax.</p>';

        $this->assertSame($input, DynamicMarkdownLinkProcessor::postprocess($input));
    }

    // Fault tolerance tests

    public function testMalformedRouteLink()
    {
        $input = '<p><a href="hyde::route(\'home">Malformed Home</a></p>';
        $expected = '<p><a href="hyde::route(\'home">Malformed Home</a></p>';

        $this->assertSame($expected, DynamicMarkdownLinkProcessor::postprocess($input));
    }

    public function testMalformedRelativeLink()
    {
        $input = '<p><a href="hyde::relativeLink(about\')">Malformed About</a></p>';
        $expected = '<p><a href="hyde::relativeLink(about\')">Malformed About</a></p>';

        $this->assertSame($expected, DynamicMarkdownLinkProcessor::postprocess($input));
    }

    public function testMalformedAssetLink()
    {
        $input = '<p><img src="hyde::asset(\'image.jpg" alt="Malformed Image" /></p>';
        $expected = '<p><img src="hyde::asset(\'image.jpg" alt="Malformed Image" /></p>';

        $this->assertSame($expected, DynamicMarkdownLinkProcessor::postprocess($input));
    }

    public function testEmptyRouteLink()
    {
        $input = '<p><a href="hyde::route()">Empty Route</a></p>';
        $expected = '<p><a href="hyde::route()">Empty Route</a></p>';

        $this->assertSame($expected, DynamicMarkdownLinkProcessor::postprocess($input));
    }

    public function testEmptyRelativeLink()
    {
        $input = '<p><a href="hyde::relativeLink()">Empty Relative Link</a></p>';
        $expected = '<p><a href="hyde::relativeLink()">Empty Relative Link</a></p>';

        $this->assertSame($expected, DynamicMarkdownLinkProcessor::postprocess($input));
    }

    public function testEmptyAssetLink()
    {
        $input = '<p><img src="hyde::asset()" alt="Empty Asset" /></p>';
        $expected = '<p><img src="hyde::asset()" alt="Empty Asset" /></p>';

        $this->assertSame($expected, DynamicMarkdownLinkProcessor::postprocess($input));
    }

    public function testMixedValidAndInvalidLinks()
    {
        $input = <<<'HTML'
        <a href="hyde::route('home')">Valid Home</a>
        <a href="hyde::route(invalid'">Invalid Route</a>
        <a href="hyde::relativeLink('about')">Valid About</a>
        <a href="hyde::relativeLink(about')">Invalid Relative</a>
        <img src="hyde::asset('logo.png')" alt="Valid Logo" />
        <img src="hyde::asset('image.jpg" alt="Invalid Asset" />
        HTML;

        $expected = <<<'HTML'
        <a href="home.html">Valid Home</a>
        <a href="hyde::route(invalid'">Invalid Route</a>
        <a href="about">Valid About</a>
        <a href="hyde::relativeLink(about')">Invalid Relative</a>
        <img src="media/logo.png" alt="Valid Logo" />
        <img src="hyde::asset('image.jpg" alt="Invalid Asset" />
        HTML;

        $this->assertSame($expected, DynamicMarkdownLinkProcessor::postprocess($input));
    }

    public function testMalformedHydeSyntax()
    {
        $input = '<p><a href="hyde:route(\'home\')">Malformed Hyde Syntax</a></p>';
        $expected = '<p><a href="hyde:route(\'home\')">Malformed Hyde Syntax</a></p>';

        $this->assertSame($expected, DynamicMarkdownLinkProcessor::postprocess($input));
    }

    public function testNonExistentRouteFunction()
    {
        $input = '<p><a href="hyde::nonexistent(\'home\')">Non-existent Function</a></p>';
        $expected = '<p><a href="hyde::nonexistent(\'home\')">Non-existent Function</a></p>';

        $this->assertSame($expected, DynamicMarkdownLinkProcessor::postprocess($input));
    }
}
