<?php

declare(strict_types=1);

namespace Hyde\Framework\Services;

use Hyde\Framework\Features\BuildTasks\BuildTask;
use Hyde\Facades\Filesystem;
use Illuminate\Console\OutputStyle;
use Illuminate\Support\Str;

/**
 * This service manages the build tasks that are called after the site has been compiled using the build command.
 *
 * @see \Hyde\Framework\Testing\Feature\Services\BuildTaskServiceTest
 * @see \Hyde\Framework\Testing\Unit\BuildTaskServiceUnitTest
 */
class BuildTaskService
{
    /** @var array<string, class-string<\Hyde\Framework\Features\BuildTasks\BuildTask>> */
    protected array $buildTasks = [];

    protected ?OutputStyle $output = null;

    public function __construct()
    {
        $this->discoverTasks();
    }

    public function runTasks(): void
    {
        foreach ($this->getTasks() as $task) {
            $this->run($task);
        }
    }

    /** @return array<class-string<\Hyde\Framework\Features\BuildTasks\BuildTask>> */
    public function getTasks(): array
    {
        return $this->buildTasks;
    }

    protected static function findTasksInAppDirectory(): array
    {
        $tasks = [];

        foreach (Filesystem::smartGlob('app/Actions/*BuildTask.php') as $file) {
            $tasks[] = str_replace(
                ['app', '.php', '/'],
                ['App', '', '\\'],
                (string) $file
            );
        }

        return $tasks;
    }

    protected function run(string $task): static
    {
        $this->runTask(new $task($this->output));

        return $this;
    }

    public function registerIf(string $task, callable|bool $condition): static
    {
        if (is_bool($condition) ? $condition : $condition()) {
            $this->registerTask($task);
        }

        return $this;
    }

    protected function runTask(BuildTask $task): static
    {
        $task->handle();

        return $this;
    }

    public function setOutput(?OutputStyle $output): static
    {
        $this->output = $output;

        return $this;
    }

    /** @param  class-string<\Hyde\Framework\Features\BuildTasks\BuildTask>  $class */
    public function registerTask(string $class): static
    {
        $this->buildTasks[$this->makeTaskIdentifier($class)] = $class;

        return $this;
    }

    protected function discoverTasks(): void
    {
        $tasks = array_unique(
            array_merge(
                config('hyde.post_build_tasks', []),
                static::findTasksInAppDirectory(),
            )
        );

        foreach ($tasks as $task) {
            $this->registerTask($task);
        }
    }

    protected function makeTaskIdentifier(string $class): string
    {
        return Str::kebab(class_basename($class));
    }
}
