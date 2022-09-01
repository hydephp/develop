<?php

namespace Hyde\Framework\Services;

use Hyde\Framework\Contracts\BuildTaskContract;
use Hyde\Framework\Hyde;
use Illuminate\Console\OutputStyle;

/**
 * @see \Hyde\Framework\Testing\Feature\Services\BuildHookServiceTest
 */
class BuildTaskService
{
    /**
     * For package developers:.
     *
     * This offers a hook for packages to add custom build tasks.
     * Make sure to add the fully qualified class name to the array
     * and doing so by merging the array, not overwriting it.
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

    protected function runTask(BuildTaskContract $task): static
    {
        $task->handle();

        return $this;
    }
}
