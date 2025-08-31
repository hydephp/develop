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
    protected function setUp(): void
    {
        parent::setUp();

        // Clean up any existing test directories
        $this->cleanupTestDirectories();
    }

    protected function tearDown(): void
    {
        // Clean up test directories after each test
        $this->cleanupTestDirectories();

        parent::tearDown();
    }

    protected function cleanupTestDirectories(): void
    {
        if (is_dir(Hyde::path('custom'))) {
            File::deleteDirectory(Hyde::path('custom'));
        }

        if (is_dir(Hyde::path('_site'))) {
            File::deleteDirectory(Hyde::path('_site'));
        }
    }

    public function testDefaultConfigValueIsEmptyString()
    {
        $this->assertSame('', config('hyde.source_root'));
    }

    public function testFilesInCustomSourceRootCanBeDiscovered()
    {
        $this->setupCustomSourceRoot();

        $this->assertCount(1, MarkdownPage::files());
        $this->assertCount(1, MarkdownPage::all());
    }

    public function testFilesInCustomSourceRootCanBeCompiled()
    {
        $this->setupCustomSourceRoot();

        $this->artisan('build');

        $this->assertFileExists(Hyde::path('_site/markdown.html'));
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
        // Ensure directories exist without throwing error if they already exist
        if (! is_dir(Hyde::path('custom'))) {
            mkdir(Hyde::path('custom'));
        }
        if (! is_dir(Hyde::path('custom/_pages'))) {
            mkdir(Hyde::path('custom/_pages'));
        }

        config(['hyde.source_root' => 'custom']);
        (new HydeServiceProvider(app()))->register();

        file_put_contents(Hyde::path('custom/_pages/markdown.md'), 'Hello, world!');
    }
}
