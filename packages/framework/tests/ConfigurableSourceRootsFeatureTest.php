<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing;

use Hyde\Framework\HydeServiceProvider;
use Hyde\Hyde;
use Hyde\Pages\MarkdownPage;
use Hyde\Testing\TestCase;
use Illuminate\Support\Facades\File;
use function app;
use function config;
use function file_put_contents;
use function mkdir;

/**
 * Test the overall functionality of the configurable source roots feature.
 *
 * Also see these tests which cover specific implementation details:
 *
 * @see \Hyde\Framework\Testing\Feature\HydeKernelTest
 * @see \Hyde\Framework\Testing\Unit\HydeServiceProviderTest
 */
class ConfigurableSourceRootsFeatureTest extends TestCase
{
    public function test_default_config_value_is_empty_string()
    {
        $this->assertSame('', config('hyde.source_root'));
    }

    public function test_files_in_custom_source_root_can_be_discovered()
    {
        $this->setupCustomSourceRoot();

        $this->assertCount(1, MarkdownPage::files());
        $this->assertCount(1, MarkdownPage::all());

        File::deleteDirectory(Hyde::path('custom'));
    }

    public function test_files_in_custom_source_root_can_be_compiled()
    {
        $this->setupCustomSourceRoot();

        $this->artisan('build');

        $this->assertFileExists(Hyde::path('_site/markdown.html'));

        File::deleteDirectory(Hyde::path('custom'));
        File::deleteDirectory(Hyde::path('_site'));
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
