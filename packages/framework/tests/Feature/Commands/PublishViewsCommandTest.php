<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature\Commands;

use Hyde\Hyde;
use Hyde\Testing\TestCase;
use Illuminate\Support\Facades\File;
use function is_dir;
use function is_dir as is_dir1;
use function is_dir as is_dir2;

/**
 * @covers \Hyde\Console\Commands\PublishViewsCommand
 */
class PublishViewsCommandTest extends TestCase
{
    public function test_command_publishes_views()
    {
        $this->artisan('publish:views all')
            ->expectsOutput('Copied [vendor/hyde/framework/resources/views/pages/404.blade.php] to [_pages/404.blade.php]')
            ->assertExitCode(0);

        $this->assertFileExists(Hyde::path('resources/views/vendor/hyde/layouts/app.blade.php'));

        if (is_dir2(Hyde::path('resources/views/vendor/hyde'))) {
            File::deleteDirectory(Hyde::path('resources/views/vendor/hyde'));
        }
    }

    public function test_command_prompts_for_input()
    {
        $this->artisan('publish:views')
            ->expectsQuestion('Which category do you want to publish?', 'all')
            ->assertExitCode(0);

        if (is_dir1(Hyde::path('resources/views/vendor/hyde'))) {
            File::deleteDirectory(Hyde::path('resources/views/vendor/hyde'));
        }
    }

    public function test_can_select_view()
    {
        $this->artisan('publish:views 404')
            ->expectsOutput('Copied [vendor/hyde/framework/resources/views/pages/404.blade.php] to [_pages/404.blade.php]')
            ->assertExitCode(0);

        $this->assertFileExists(Hyde::path('_pages/404.blade.php'));

        if (is_dir(Hyde::path('resources/views/vendor/hyde'))) {
            File::deleteDirectory(Hyde::path('resources/views/vendor/hyde'));
        }
    }
}
