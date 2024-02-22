<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature\Commands;

use Hyde\Hyde;
use Hyde\Testing\TestCase;
use Illuminate\Support\Facades\File;

/**
 * @covers \Hyde\Console\Commands\PublishViewsCommand
 */
class PublishViewsCommandTest extends TestCase
{
    public function testCommandPublishesViews()
    {
        $path = str_replace('\\', '/', Hyde::pathToRelative(realpath(Hyde::vendorPath('resources/views/pages/404.blade.php'))));
        $this->artisan('publish:views')
            ->expectsQuestion('Which category do you want to publish?', 'all')
            ->expectsOutputToContain("Copying file [$path] to [_pages/404.blade.php]")
            ->assertExitCode(0);

        $this->assertFileExists(Hyde::path('resources/views/vendor/hyde/layouts/app.blade.php'));

        if (is_dir(Hyde::path('resources/views/vendor/hyde'))) {
            File::deleteDirectory(Hyde::path('resources/views/vendor/hyde'));
        }
    }

    public function testCanSelectView()
    {
        $path = str_replace('\\', '/', Hyde::pathToRelative(realpath(Hyde::vendorPath('resources/views/pages/404.blade.php'))));
        $this->artisan('publish:views page-404')
            ->expectsOutputToContain("Copying file [$path] to [_pages/404.blade.php]")
            ->assertExitCode(0);

        $this->assertFileExists(Hyde::path('_pages/404.blade.php'));

        if (is_dir(Hyde::path('resources/views/vendor/hyde'))) {
            File::deleteDirectory(Hyde::path('resources/views/vendor/hyde'));
        }
    }

    public function testWithInvalidSuppliedTag()
    {
        $this->artisan('publish:views invalid')
            ->expectsOutputToContain('No publishable resources for tag [invalid].')
            ->assertExitCode(0);
    }
}
