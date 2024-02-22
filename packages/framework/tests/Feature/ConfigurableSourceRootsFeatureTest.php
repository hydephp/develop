<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature;

use Hyde\Framework\HydeServiceProvider;
use Hyde\Hyde;
use Hyde\Pages\MarkdownPage;
use Hyde\Testing\TestCase;
use Illuminate\Support\Facades\File;

/**
 * Test the overall functionality of the configurable source roots feature.
 *
 * Also see these tests which cover specific implementation details:
 *
 * @see \Hyde\Framework\Testing\Feature\HydeKernelTest
 * @see \Hyde\Framework\Testing\Feature\HydeServiceProviderTest
 */
class ConfigurableSourceRootsFeatureTest extends TestCase
{
    public function testDefaultConfigValueIsEmptyString()
    {
        $this->assertSame('', config('hyde.source_root'));
    }

    public function testFilesInCustomSourceRootCanBeDiscovered()
    {
        $this->setupCustomSourceRoot();

        $this->assertCount(1, MarkdownPage::files());
        $this->assertCount(1, MarkdownPage::all());

        File::deleteDirectory(Hyde::path('custom'));
    }

    public function testFilesInCustomSourceRootCanBeCompiled()
    {
        $this->setupCustomSourceRoot();

        $this->artisan('build');

        $this->assertFileExists(Hyde::path('_site/markdown.html'));

        File::deleteDirectory(Hyde::path('custom'));
        File::deleteDirectory(Hyde::path('_site'));
    }

    public function testHydePagePathMethodSupportsCustomSourceRoots()
    {
        config(['hyde.source_root' => 'custom']);
        (new HydeServiceProvider(app()))->register();

        $this->assertSame(
            Hyde::path('custom/_pages/foo.md'), MarkdownPage::path('foo.md')
        );
    }

    protected function setupCustomSourceRoot(): void
    {
        mkdir(Hyde::path('custom'));
        mkdir(Hyde::path('custom/_pages'));

        config(['hyde.source_root' => 'custom']);
        (new HydeServiceProvider(app()))->register();

        file_put_contents(Hyde::path('custom/_pages/markdown.md'), 'Hello, world!');
    }
}
