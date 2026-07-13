<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature;

use Hyde\Testing\TestCase;
use Hyde\Pages\MarkdownPage;
use Hyde\Support\Models\Route;
use Hyde\Support\Models\Redirect;
use Hyde\Foundation\Facades\Routes;
use Hyde\Framework\Features\TextGenerators\LlmsTxtGenerator;

#[\PHPUnit\Framework\Attributes\CoversClass(\Hyde\Framework\Features\TextGenerators\LlmsTxtGenerator::class)]
class LlmsTxtGeneratorTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->withSiteUrl();
    }

    public function testGeneratesFileWithSiteNameAsHeading()
    {
        config(['hyde.name' => 'My Site']);

        $this->assertSame(<<<'TXT'
        # My Site

        ## Pages

        - [Index](https://example.com/index.html)

        TXT, $this->generate());
    }

    public function testGeneratesSummaryBlockquoteWhenDescriptionIsConfigured()
    {
        config(['hyde.llms.description' => 'Everything about the example project.']);

        $this->assertStringContainsString("# HydePHP\n\n> Everything about the example project.\n\n## Pages", $this->generate());
    }

    public function testOmitsSummaryBlockquoteWhenNoDescriptionIsConfigured()
    {
        $this->assertStringStartsWith("# HydePHP\n\n## Pages", $this->generate());
    }

    public function testGroupsPagesIntoSectionsByPageType()
    {
        $this->file('_pages/about.md', '# About');
        $this->file('_docs/installation.md', '# Installation');
        $this->file('_posts/hello-world.md', '# Hello World');

        $this->assertSame(<<<'TXT'
        # HydePHP

        ## Pages

        - [Index](https://example.com/index.html)
        - [About](https://example.com/about.html)

        ## Documentation

        - [Installation](https://example.com/docs/installation.html)

        ## Blog Posts

        - [Hello World](https://example.com/posts/hello-world.html)

        TXT, $this->generate());
    }

    public function testEmptySectionsAreOmitted()
    {
        $this->assertStringNotContainsString('## Documentation', $this->generate());
        $this->assertStringNotContainsString('## Blog Posts', $this->generate());
    }

    public function testUsesPageAbstractAsLinkDescription()
    {
        $this->markdown('_docs/installation.md', '# Installation', ['abstract' => 'How to install the project.']);

        $this->assertStringContainsString('- [Installation](https://example.com/docs/installation.html): How to install the project.', $this->generate());
    }

    public function testFallsBackToPageDescriptionWhenThereIsNoAbstract()
    {
        $this->markdown('_docs/installation.md', '# Installation', ['description' => 'The meta description.']);

        $this->assertStringContainsString('- [Installation](https://example.com/docs/installation.html): The meta description.', $this->generate());
    }

    public function testPrefersTheAbstractOverTheDescription()
    {
        $this->markdown('_docs/installation.md', '# Installation', [
            'abstract' => 'The abstract.',
            'description' => 'The meta description.',
        ]);

        $this->assertStringContainsString('- [Installation](https://example.com/docs/installation.html): The abstract.', $this->generate());
    }

    public function testMultiLineDescriptionsAreCollapsedToASingleLine()
    {
        $this->file('_docs/installation.md', "---\nabstract: |\n  First line.\n  Second line.\n---\n\n# Installation");

        $this->assertStringContainsString('- [Installation](https://example.com/docs/installation.html): First line. Second line.', $this->generate());
    }

    public function testPagesCanOptOutUsingFrontMatter()
    {
        $this->markdown('_pages/private.md', '# Private', ['llms' => false]);

        $this->assertStringNotContainsString('Private', $this->generate());
    }

    public function testPagesExcludedFromTheSitemapAreNotListed()
    {
        $this->markdown('_pages/private.md', '# Private', ['sitemap' => false]);

        $this->assertStringNotContainsString('Private', $this->generate());
    }

    public function testPagesExcludedFromTheSitemapCanBeAddedBackUsingFrontMatter()
    {
        $this->markdown('_pages/private.md', '# Private', ['sitemap' => false, 'llms' => true]);

        $this->assertStringContainsString('- [Private](https://example.com/private.html)', $this->generate());
    }

    public function testErrorPagesAreNotListed()
    {
        $this->assertStringNotContainsString('404', $this->generate());
    }

    public function testGeneratedNonHtmlPagesAreNotListed()
    {
        $contents = $this->generate();

        $this->assertStringNotContainsString('llms.txt', $contents);
        $this->assertStringNotContainsString('robots.txt', $contents);
        $this->assertStringNotContainsString('sitemap.xml', $contents);
    }

    public function testVirtualPagesLikeTheDocumentationSearchPageAreNotListed()
    {
        $this->file('_docs/installation.md', '# Installation');

        $contents = $this->generate();

        $this->assertStringContainsString('- [Installation](https://example.com/docs/installation.html)', $contents);
        $this->assertStringNotContainsString('docs/search', $contents);
    }

    public function testRedirectsAreNotListed()
    {
        Routes::addRoute(new Route(new Redirect('old-page', 'new-page')));

        $this->assertStringNotContainsString('old-page', $this->generate());
    }

    public function testCustomPageClassesAreListedUnderTheirParentClassSection()
    {
        Routes::addRoute(new Route(new LlmsTxtGeneratorTestPage('custom')));

        $this->assertStringContainsString("## Pages\n\n- [Index](https://example.com/index.html)\n- [Custom](https://example.com/custom.html)", $this->generate());
    }

    public function testPrettyUrlsAreUsedWhenEnabled()
    {
        config(['hyde.pretty_urls' => true]);

        $this->file('_docs/installation.md', '# Installation');

        $this->assertStringContainsString('- [Installation](https://example.com/docs/installation)', $this->generate());
    }

    protected function generate(): string
    {
        return (new LlmsTxtGenerator())->generate();
    }
}

class LlmsTxtGeneratorTestPage extends MarkdownPage
{
    //
}
