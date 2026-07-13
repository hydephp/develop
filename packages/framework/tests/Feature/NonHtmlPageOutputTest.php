<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature;

use Hyde\Hyde;
use Hyde\Testing\TestCase;
use Hyde\Pages\InMemoryPage;
use Hyde\Foundation\HydeKernel;
use Hyde\Foundation\Facades\Routes;
use Illuminate\Support\Facades\File;
use Hyde\Framework\Actions\StaticPageBuilder;

/**
 * Feature test for compiling in-memory pages with non-HTML output file extensions,
 * covering the user path of registering pages like robots.txt in code and having
 * them compiled to their declared output paths by the standard build process.
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

    public function testBuildCommandCompilesInMemoryPageWithTxtExtensionToDeclaredOutputPath()
    {
        Hyde::kernel()->booting(function (HydeKernel $kernel): void {
            $kernel->pages()->addPage(new InMemoryPage('robots.txt', contents: "User-agent: *\nAllow: /"));
        });

        $this->artisan('build')->assertExitCode(0);

        $this->assertFileExists(Hyde::path('_site/robots.txt'));
        $this->assertSame("User-agent: *\nAllow: /", file_get_contents(Hyde::path('_site/robots.txt')));
        $this->assertFileDoesNotExist(Hyde::path('_site/robots.txt.html'));
    }

    public function testBuildCommandCompilesInMemoryPageWithJsonExtensionToDeclaredOutputPath()
    {
        Hyde::kernel()->booting(function (HydeKernel $kernel): void {
            $kernel->pages()->addPage(new InMemoryPage('data.json', contents: '{"foo": "bar"}'));
        });

        $this->artisan('build')->assertExitCode(0);

        $this->assertFileExists(Hyde::path('_site/data.json'));
        $this->assertSame('{"foo": "bar"}', file_get_contents(Hyde::path('_site/data.json')));
    }

    public function testBuildCommandCompilesInMemoryPageWithXmlExtensionToDeclaredOutputPath()
    {
        Hyde::kernel()->booting(function (HydeKernel $kernel): void {
            $kernel->pages()->addPage(new InMemoryPage('custom.xml', contents: '<?xml version="1.0"?><foo/>'));
        });

        $this->artisan('build')->assertExitCode(0);

        $this->assertFileExists(Hyde::path('_site/custom.xml'));
        $this->assertSame('<?xml version="1.0"?><foo/>', file_get_contents(Hyde::path('_site/custom.xml')));
    }

    public function testBuildCommandCompilesInMemoryPageWithDeclaredExtensionInNestedPath()
    {
        Hyde::kernel()->booting(function (HydeKernel $kernel): void {
            $kernel->pages()->addPage(new InMemoryPage('foo/bar.txt', contents: 'baz'));
        });

        $this->artisan('build')->assertExitCode(0);

        $this->assertFileExists(Hyde::path('_site/foo/bar.txt'));
        $this->assertSame('baz', file_get_contents(Hyde::path('_site/foo/bar.txt')));
    }

    public function testInMemoryPageWithDeclaredExtensionIsRegisteredAsRoute()
    {
        Hyde::kernel()->booting(function (HydeKernel $kernel): void {
            $kernel->pages()->addPage(new InMemoryPage('robots.txt', contents: 'User-agent: *'));
        });

        $this->assertTrue(Routes::exists('robots.txt'));
        $this->assertSame('robots.txt', Routes::get('robots.txt')->getOutputPath());
    }

    public function testStaticPageBuilderCompilesInMemoryPageWithDeclaredExtension()
    {
        StaticPageBuilder::handle(new InMemoryPage('llms.txt', contents: '# Hello World'));

        $this->assertFileExists(Hyde::path('_site/llms.txt'));
        $this->assertSame('# Hello World', file_get_contents(Hyde::path('_site/llms.txt')));
    }

    public function testInMemoryPageWithDeclaredExtensionCanCompileUsingView()
    {
        $this->file('_pages/robots.blade.php', 'User-agent: {{ $agent }}');

        StaticPageBuilder::handle(new InMemoryPage('robots.txt', ['agent' => '*'], view: 'robots'));

        $this->assertFileExists(Hyde::path('_site/robots.txt'));
        $this->assertSame('User-agent: *', file_get_contents(Hyde::path('_site/robots.txt')));
    }
}
