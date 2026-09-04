<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature;

use Hyde\Framework\HydeServiceProvider;
use Hyde\Framework\Features\XmlGenerators\SitemapGenerator;
use Hyde\Hyde;
use Hyde\Pages\MarkdownPost;
use Hyde\Testing\TestCase;
use Illuminate\Support\ServiceProvider;

#[\PHPUnit\Framework\Attributes\CoversClass(\Hyde\Foundation\Concerns\ManagesExtensions::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(\Hyde\Foundation\Kernel\FileCollection::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(\Hyde\Framework\Actions\SourceFileParser::class)]
class PageClassReplacementTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config(['hyde.generate_sitemap' => false, 'hyde.rss.enabled' => false]);
    }

    public function testServiceProviderCanReplaceADiscoveredPageClassBeforeKernelBoot()
    {
        (new PageClassReplacementServiceProvider(app()))->register();
        $this->markdown('_posts/2024-01-02-hello-world.md', 'Hello world', ['title' => 'Hello World']);

        Hyde::boot();

        $this->assertSame([
            \Hyde\Pages\HtmlPage::class,
            \Hyde\Pages\BladePage::class,
            \Hyde\Pages\MarkdownPage::class,
            TestMarkdownPost::class,
            \Hyde\Pages\DocumentationPage::class,
        ], Hyde::getRegisteredPageClasses());

        $this->assertNotContains(MarkdownPost::class, Hyde::getRegisteredPageClasses());
        $this->assertCount(1, Hyde::files()->getFiles(MarkdownPost::class));
        $this->assertCount(1, Hyde::files()->getFiles(TestMarkdownPost::class));

        $sourceFile = Hyde::files()->getFile('_posts/2024-01-02-hello-world.md');
        $page = Hyde::pages()->getPage('_posts/2024-01-02-hello-world.md');

        $this->assertSame(TestMarkdownPost::class, $sourceFile->pageClass);
        $this->assertSame(TestMarkdownPost::class, $page::class);
        $this->assertInstanceOf(TestMarkdownPost::class, $page);
        $this->assertInstanceOf(MarkdownPost::class, $page);
        $this->assertSame(2, $page->readingTime());
        $this->assertSame('Custom: Hello World', $page->title());
        $this->assertSame('posts/hello-world', $page->getRouteKey());
        $this->assertSame('_posts', TestMarkdownPost::sourceDirectory());
        $this->assertSame('posts', TestMarkdownPost::outputDirectory());

        $sitemap = (new SitemapGenerator())->generate()->getXmlElement();
        $postEntry = collect($sitemap->url)->first(
            fn ($url): bool => (string) $url->loc === 'posts/hello-world.html'
        );

        $this->assertNotNull($postEntry);
        $this->assertSame('0.75', (string) $postEntry->priority);
    }

    public function testCanonicalAndReplacementStaticQueriesUseTheDiscoveredSubclass()
    {
        Hyde::replacePageClass(MarkdownPost::class, TestMarkdownPost::class);
        $this->markdown('_posts/query-test.md', 'Hello world');

        Hyde::boot();

        $this->assertSame(['query-test'], MarkdownPost::files());
        $this->assertSame(['query-test'], TestMarkdownPost::files());
        $this->assertContainsOnlyInstancesOf(TestMarkdownPost::class, MarkdownPost::all());
        $this->assertContainsOnlyInstancesOf(TestMarkdownPost::class, TestMarkdownPost::all());
        $this->assertInstanceOf(TestMarkdownPost::class, MarkdownPost::get('query-test'));
        $this->assertInstanceOf(TestMarkdownPost::class, TestMarkdownPost::get('query-test'));
        $this->assertInstanceOf(TestMarkdownPost::class, MarkdownPost::parse('query-test'));
        $this->assertInstanceOf(TestMarkdownPost::class, TestMarkdownPost::parse('query-test'));
    }

    public function testCanonicalDirectoryConfigurationStillAppliesToReplacementClass()
    {
        config(['hyde.source_directories' => [MarkdownPost::class => '.replacement/posts']]);
        config(['hyde.output_directories' => [MarkdownPost::class => 'articles']]);

        (new HydeServiceProvider(app()))->register();
        (new PageClassReplacementServiceProvider(app()))->register();
        $this->markdown('.replacement/posts/configured.md', 'Configured post');

        Hyde::boot();

        $page = MarkdownPost::get('configured');

        $this->assertInstanceOf(TestMarkdownPost::class, $page);
        $this->assertSame('.replacement/posts/configured.md', $page->getSourcePath());
        $this->assertSame('articles/configured.html', $page->getOutputPath());
    }
}

class PageClassReplacementServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        Hyde::replacePageClass(MarkdownPost::class, TestMarkdownPost::class);
    }
}

class TestMarkdownPost extends MarkdownPost
{
    public function readingTime(): int
    {
        return str_word_count($this->markdown->body());
    }

    public function title(): string
    {
        return 'Custom: '.$this->title;
    }
}
