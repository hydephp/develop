<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature;

use Hyde\Testing\TestCase;
use Hyde\Pages\MarkdownPage;
use Hyde\Pages\MarkdownPost;
use Hyde\Pages\DocumentationPage;
use Hyde\Support\Models\Route;
use Hyde\Support\Models\Redirect;
use Hyde\Foundation\Facades\Routes;
use Hyde\Framework\Exceptions\InvalidConfigurationException;
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

    public function testGroupsPagesIntoTheirConfiguredSections()
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

    public function testSectionsFollowTheOrderOfTheConfiguredMap()
    {
        config(['hyde.llms.sections' => [
            MarkdownPost::class => 'Blog Posts',
            DocumentationPage::class => 'Documentation',
        ]]);

        $this->file('_docs/installation.md', '# Installation');
        $this->file('_posts/hello-world.md', '# Hello World');

        $this->assertSame(<<<'TXT'
        # HydePHP

        ## Blog Posts

        - [Hello World](https://example.com/posts/hello-world.html)

        ## Documentation

        - [Installation](https://example.com/docs/installation.html)

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

    public function testRedirectsAreNotListed()
    {
        config(['hyde.llms.sections' => [Redirect::class => 'Redirects']]);

        Routes::addRoute(new Route(new Redirect('old-page', 'new-page')));

        $this->assertStringNotContainsString('old-page', $this->generate());
    }

    public function testPageTypesNotInTheSectionsConfigAreNotListed()
    {
        config(['hyde.llms.sections' => [DocumentationPage::class => 'Documentation']]);

        $this->file('_posts/hello-world.md', '# Hello World');
        $this->file('_docs/installation.md', '# Installation');

        $contents = $this->generate();

        $this->assertStringContainsString('Installation', $contents);
        $this->assertStringNotContainsString('Hello World', $contents);
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

    public function testSectionKeyThatIsNotAPageClassFailsWithConfigurationException()
    {
        config(['hyde.llms.sections' => ['NotAPageClass' => 'Pages']]);

        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessage('Invalid `hyde.llms.sections` entry at index [NotAPageClass]: each key must be a page class extending Hyde\Pages\Concerns\HydePage.');

        $this->generate();
    }

    public function testNonStringSectionHeadingFailsWithConfigurationException()
    {
        config(['hyde.llms.sections' => [MarkdownPage::class => 123]]);

        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessage('Invalid `hyde.llms.sections` entry at index [Hyde\Pages\MarkdownPage]: each section heading must be a non-empty string, int given.');

        $this->generate();
    }

    public function testEmptySectionHeadingFailsWithConfigurationException()
    {
        config(['hyde.llms.sections' => [MarkdownPage::class => '']]);

        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessage('Invalid `hyde.llms.sections` entry at index [Hyde\Pages\MarkdownPage]: each section heading must be a non-empty string, string given.');

        $this->generate();
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
