<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature;

use Hyde\Hyde;
use Hyde\Testing\TestCase;
use Hyde\Pages\InMemoryPage;
use Hyde\Foundation\HydeKernel;
use Hyde\Foundation\Facades\Routes;
use Hyde\Foundation\Concerns\HydeExtension;
use Hyde\Pages\Concerns\HydePage;
use Illuminate\Support\Facades\File;
use Hyde\Framework\Actions\StaticPageBuilder;

/**
 * Feature test for compiling custom page classes with non-HTML output extensions.
 *
 * @see \Hyde\Framework\Testing\Unit\Pages\InMemoryPageTest
 */
#[\PHPUnit\Framework\Attributes\CoversClass(\Hyde\Pages\InMemoryPage::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(\Hyde\Pages\Concerns\HydePage::class)]
class NonHtmlPageOutputTest extends TestCase
{
    protected function tearDown(): void
    {
        File::cleanDirectory(Hyde::path('_site'));

        parent::tearDown();
    }

    public function testBuildCommandCompilesInMemoryPageWithTxtIdentifier()
    {
        Hyde::kernel()->booting(function (HydeKernel $kernel): void {
            $kernel->pages()->addPage(InMemoryPage::make('robots.txt', contents: "User-agent: *\nAllow: /"));
        });

        $this->artisan('build')->assertExitCode(0);

        $this->assertFileExists(Hyde::path('_site/robots.txt'));
        $this->assertSame("User-agent: *\nAllow: /", file_get_contents(Hyde::path('_site/robots.txt')));
        $this->assertFileDoesNotExist(Hyde::path('_site/robots.txt.html'));
    }

    public function testBuildCommandCompilesNestedNonHtmlOutputPath()
    {
        Hyde::kernel()->booting(function (HydeKernel $kernel): void {
            $kernel->pages()->addPage(InMemoryPage::make('foo/bar.txt', contents: 'baz'));
        });

        $this->artisan('build')->assertExitCode(0);

        $this->assertFileExists(Hyde::path('_site/foo/bar.txt'));
        $this->assertSame('baz', file_get_contents(Hyde::path('_site/foo/bar.txt')));
    }

    public function testNonHtmlInMemoryPageIsRegisteredAsRoute()
    {
        Hyde::kernel()->booting(function (HydeKernel $kernel): void {
            $kernel->pages()->addPage(InMemoryPage::make('robots.txt', contents: 'User-agent: *'));
        });

        $this->assertTrue(Routes::exists('robots.txt'));
        $this->assertSame('robots.txt', Routes::get('robots.txt')->getOutputPath());
    }

    public function testStaticPageBuilderCompilesNonHtmlInMemoryPage()
    {
        StaticPageBuilder::handle(InMemoryPage::make('llms.txt', contents: '# Hello World'));

        $this->assertFileExists(Hyde::path('_site/llms.txt'));
        $this->assertSame('# Hello World', file_get_contents(Hyde::path('_site/llms.txt')));
    }

    public function testNonHtmlInMemoryPageCanCompileUsingView()
    {
        $this->file('_pages/robots.blade.php', 'User-agent: {{ $agent }}');

        StaticPageBuilder::handle(InMemoryPage::make('robots.txt', ['agent' => '*'], view: 'robots'));

        $this->assertFileExists(Hyde::path('_site/robots.txt'));
        $this->assertSame('User-agent: *', file_get_contents(Hyde::path('_site/robots.txt')));
    }

    public function testBuildCommandExcludesNonHtmlInMemoryPageFromSitemap()
    {
        $this->withSiteUrl();

        Hyde::kernel()->booting(function (HydeKernel $kernel): void {
            $kernel->pages()->addPage(InMemoryPage::make('robots.txt', contents: 'User-agent: *'));
        });

        $this->artisan('build')->assertExitCode(0);

        $this->assertFileExists(Hyde::path('_site/robots.txt'));
        $this->assertFileExists(Hyde::path('_site/sitemap.xml'));
        $this->assertStringNotContainsString('robots.txt', file_get_contents(Hyde::path('_site/sitemap.xml')));
    }

    public function testBuildCommandCompilesDiscoverableCustomPageClassWithNonHtmlOutputExtension()
    {
        $this->directory('_leaves');
        $this->file('_leaves/hello.md', 'Hello World');

        Hyde::kernel()->registerExtension(NonHtmlPageTestExtension::class);

        $this->assertSame(['hello'], DiscoverableNonHtmlTestPage::files());
        $this->assertTrue(Routes::exists('hello.txt'));

        $this->artisan('build')->assertExitCode(0);

        $this->assertFileExists(Hyde::path('_site/hello.txt'));
        $this->assertSame('Hello World', file_get_contents(Hyde::path('_site/hello.txt')));
        $this->assertFileDoesNotExist(Hyde::path('_site/hello.txt.html'));
        $this->assertFileDoesNotExist(Hyde::path('_site/hello.html'));
        $this->assertFileDoesNotExist(Hyde::path('_site/hello.md.html'));
    }
}

class DiscoverableNonHtmlTestPage extends HydePage
{
    public static string $sourceDirectory = '_leaves';
    public static string $outputDirectory = '';
    public static string $sourceExtension = '.md';
    public static string $outputExtension = '.txt';

    public function compile(): string
    {
        return file_get_contents(Hyde::path($this->getSourcePath()));
    }
}

class NonHtmlPageTestExtension extends HydeExtension
{
    public static function getPageClasses(): array
    {
        return [DiscoverableNonHtmlTestPage::class];
    }
}
