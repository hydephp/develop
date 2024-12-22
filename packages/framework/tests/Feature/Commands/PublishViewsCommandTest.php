<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature\Commands;

use Hyde\Console\Helpers\ConsoleHelper;
use Hyde\Hyde;
use Hyde\Testing\TestCase;
use Illuminate\Support\Facades\File;

/**
 * @covers \Hyde\Console\Commands\PublishViewsCommand
 *
 * @see \Hyde\Framework\Testing\Unit\InteractivePublishCommandHelperTest
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

    public function testInteractiveSelectionOnUnixSystems()
    {
        ConsoleHelper::mockWindowsOs(false);

        ConsoleHelper::mockMultiselect(['resources/views/vendor/hyde/components/article-excerpt.blade.php'], function ($label, $options) {
            $this->assertEquals('Select the files you want to publish (CTRL+A to toggle all)', $label);
            $this->assertContainsOnly('string', array_keys($options));
            $this->assertContainsOnly('string', array_values($options));
            $this->assertContains('resources/views/vendor/hyde/components/article-excerpt.blade.php', array_keys($options));
            $this->assertContains('article-excerpt.blade.php', array_values($options));
        });

        $this->artisan('publish:views components --interactive')
            ->expectsOutput('Published files [article-excerpt.blade.php]')
            ->assertExitCode(0);

        $this->assertFileExists(Hyde::path('resources/views/vendor/hyde/components/article-excerpt.blade.php'));

        File::deleteDirectory(Hyde::path('resources/views/vendor/hyde'));
    }

    public function testInteractiveSelectionOnWindowsSystems()
    {
        ConsoleHelper::mockWindowsOs();

        $this->artisan('publish:views components --interactive')
            ->expectsOutput('Due to limitations in the Windows version of PHP, it is not currently possible to use interactive mode on Windows outside of WSL.')
            ->assertExitCode(1);

        File::deleteDirectory(Hyde::path('resources/views/vendor/hyde'));
    }

    protected function tearDown(): void
    {
        ConsoleHelper::clearMocks();
        
        parent::tearDown();
    }
}
