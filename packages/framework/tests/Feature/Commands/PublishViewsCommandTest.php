<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature\Commands;

use Hyde\Console\Helpers\ConsoleHelper;
use Hyde\Facades\Filesystem;
use Hyde\Hyde;
use Hyde\Testing\TestCase;
use Illuminate\Support\Facades\File;

/**
 * @covers \Hyde\Console\Commands\PublishViewsCommand
 * @covers \Hyde\Console\Helpers\InteractivePublishCommandHelper
 *
 * @see \Hyde\Framework\Testing\Unit\InteractivePublishCommandHelperTest
 */
class PublishViewsCommandTest extends TestCase
{
    public function testCommandPublishesViews()
    {
        $count = Filesystem::findFiles('vendor/hyde/framework/resources/views/components', '.blade.php', true)->count()
            + Filesystem::findFiles('vendor/hyde/framework/resources/views/layouts', '.blade.php', true)->count();

        $this->artisan('publish:views')
            ->expectsQuestion('Which category do you want to publish?', 'all')
            ->doesntExpectOutputToContain('Selected category')
            ->expectsOutput("Published all $count files to [resources/views/vendor/hyde]")
            ->assertExitCode(0);

        // Assert all groups were published
        $this->assertDirectoryExists(Hyde::path('resources/views/vendor/hyde'));
        $this->assertDirectoryExists(Hyde::path('resources/views/vendor/hyde/layouts'));
        $this->assertDirectoryExists(Hyde::path('resources/views/vendor/hyde/components'));

        // Assert files were published
        $this->assertFileExists(Hyde::path('resources/views/vendor/hyde/layouts/app.blade.php'));
        $this->assertFileExists(Hyde::path('resources/views/vendor/hyde/layouts/page.blade.php'));
        $this->assertFileExists(Hyde::path('resources/views/vendor/hyde/components/article-excerpt.blade.php'));

        // Assert subdirectories were published with files
        $this->assertDirectoryExists(Hyde::path('resources/views/vendor/hyde/components/docs'));
        $this->assertFileExists(Hyde::path('resources/views/vendor/hyde/components/docs/documentation-article.blade.php'));
    }

    public function testCanSelectGroupWithArgument()
    {
        $this->artisan('publish:views layouts --no-interaction')
            ->expectsOutput("Published all [layout] files to [resources/views/vendor/hyde/layouts]")
            ->assertExitCode(0);

        // Assert selected group was published
        $this->assertDirectoryExists(Hyde::path('resources/views/vendor/hyde'));
        $this->assertDirectoryExists(Hyde::path('resources/views/vendor/hyde/layouts'));

        // Assert files were published
        $this->assertFileExists(Hyde::path('resources/views/vendor/hyde/layouts/app.blade.php'));
        $this->assertFileExists(Hyde::path('resources/views/vendor/hyde/layouts/page.blade.php'));

        // Assert not selected group was not published
        $this->assertDirectoryDoesNotExist(Hyde::path('resources/views/vendor/hyde/components'));
        $this->assertFileDoesNotExist(Hyde::path('resources/views/vendor/hyde/components/article-excerpt.blade.php'));
    }

    public function testWithInvalidSuppliedTag()
    {
        $this->artisan('publish:views invalid')
            ->expectsOutput("Invalid selection: 'invalid'")
            ->expectsOutput('Allowed values are: [all, layouts, components]')
            ->assertExitCode(1);
    }

    protected function tearDown(): void
    {
        ConsoleHelper::clearMocks();

        if (File::isDirectory(Hyde::path('resources/views/vendor'))) {
            File::deleteDirectory(Hyde::path('resources/views/vendor'));
        }

        parent::tearDown();
    }
}
