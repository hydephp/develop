<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature\Commands;

use Hyde\Facades\Filesystem;
use Hyde\Hyde;
use Hyde\Testing\TestCase;
use Illuminate\Support\Facades\File;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * Step 7 (§8): publish:views is now a thin, deprecated delegator to `php hyde publish`.
 * It prints a one-line deprecation notice and forwards the group to the matching scope flag
 * (layouts → --layouts, components → --components, no group → --all).
 *
 * @see \Hyde\Framework\Testing\Feature\Commands\PublishCommandViewsTest for the real views flow.
 */
#[CoversClass(\Hyde\Console\Commands\PublishViewsCommand::class)]
class PublishViewsCommandTest extends TestCase
{
    public function testWithoutGroupPrintsNoticeAndDelegatesToPublishAll()
    {
        $count = $this->viewCount('layouts') + $this->viewCount('components');

        $this->artisan('publish:views --no-interaction')
            ->expectsOutputToContain('publish:views is deprecated. Use php hyde publish --all instead.')
            ->expectsOutputToContain("Published all $count views to [resources/views/vendor/hyde]")
            ->assertExitCode(0);

        $this->assertFileExists(Hyde::path('resources/views/vendor/hyde/layouts/app.blade.php'));
        $this->assertFileExists(Hyde::path('resources/views/vendor/hyde/components/article-excerpt.blade.php'));
    }

    public function testLayoutsGroupPrintsNoticeAndDelegatesToLayoutsFlag()
    {
        $count = $this->viewCount('layouts');

        $this->artisan('publish:views layouts --no-interaction')
            ->expectsOutputToContain('publish:views is deprecated. Use php hyde publish --layouts instead.')
            ->expectsOutputToContain("Published all $count views to [resources/views/vendor/hyde/layouts]")
            ->assertExitCode(0);

        $this->assertFileExists(Hyde::path('resources/views/vendor/hyde/layouts/app.blade.php'));
        $this->assertDirectoryDoesNotExist(Hyde::path('resources/views/vendor/hyde/components'));
    }

    public function testComponentsGroupPrintsNoticeAndDelegatesToComponentsFlag()
    {
        $count = $this->viewCount('components');

        $this->artisan('publish:views components --no-interaction')
            ->expectsOutputToContain('publish:views is deprecated. Use php hyde publish --components instead.')
            ->expectsOutputToContain("Published all $count views to [resources/views/vendor/hyde/components]")
            ->assertExitCode(0);

        $this->assertFileExists(Hyde::path('resources/views/vendor/hyde/components/article-excerpt.blade.php'));
        $this->assertDirectoryDoesNotExist(Hyde::path('resources/views/vendor/hyde/layouts'));
    }

    public function testInvalidGroupFailsWithoutPublishingViews()
    {
        $this->artisan('publish:views typo_group --no-interaction')
            ->expectsOutputToContain("Invalid selection: 'typo_group'")
            ->expectsOutputToContain('Allowed values are: [layouts, components]')
            ->assertExitCode(1);

        $this->assertDirectoryDoesNotExist(Hyde::path('resources/views/vendor'));
    }

    protected function viewCount(string $group): int
    {
        return Filesystem::findFiles("packages/framework/resources/views/$group", '.blade.php', true)->count();
    }

    protected function tearDown(): void
    {
        if (File::isDirectory(Hyde::path('resources/views/vendor'))) {
            File::deleteDirectory(Hyde::path('resources/views/vendor'));
        }

        parent::tearDown();
    }
}
