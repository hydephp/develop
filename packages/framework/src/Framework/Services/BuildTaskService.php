<?php

declare(strict_types=1);

namespace Hyde\Framework\Services;

use Hyde\Framework\Features\BuildTasks\BuildTask;
use Hyde\Framework\Features\BuildTasks\PostBuildTasks\GenerateBuildManifest;
use Hyde\Hyde;
use Illuminate\Console\OutputStyle;

/**
 * This service manages the build tasks that are called after the site has been compiled using the build command.
 *
 * @see \Hyde\Framework\Testing\Feature\Services\BuildTaskServiceTest
 */
class BuildTaskService
{
    /**
     * Information for package developers: This offers a hook for packages to add custom build tasks.
     * Make sure to add the fully qualified class name to the array and doing so by merging the array, not overwriting it.
     */
    public static array $postBuildTasks = [];

    protected ?OutputStyle $output;

    public function __construct(?OutputStyle $output = null)
    {
        $this->output = $output;
    }

    public function runPostBuildTasks(): void
    {
        foreach ($this->getPostBuildTasks() as $task) {
            $this->run($task);
        }

        $this->runIf(GenerateBuildManifest::class, config('hyde.generate_build_manifest', true));
    }

    public function getPostBuildTasks(): array
    {
        return array_unique(
            array_merge(
                config('hyde.post_build_tasks', []),
                static::findTasksInAppDirectory(),
                static::$postBuildTasks
            )
        );
    }

    public static function findTasksInAppDirectory(): array
    {
        $tasks = [];

        foreach (glob(Hyde::path('app/Actions/*BuildTask.php')) as $file) {
            $tasks[] = str_replace(
                [Hyde::path('app'), '.php', '/'],
                ['App', '', '\\'],
                $file
            );
        }

        return $tasks;
    }

    public function run(string $task): static
    {
        $this->runTask(new $task($this->output));

        return $this;
    }

    public function runIf(string $task, callable|bool $condition): static
    {
        if (is_bool($condition) ? $condition : $condition()) {
            $this->run($task);
        }

        return $this;
    }

    protected function runTask(BuildTask $task): static
    {
        $task->handle();

        return $this;
    }
}
