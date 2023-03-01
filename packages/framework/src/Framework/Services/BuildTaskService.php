<?php

declare(strict_types=1);

namespace Hyde\Framework\Services;

use Hyde\Facades\Config;
use Hyde\Facades\Filesystem;
use Hyde\Framework\Features\BuildTasks\BuildTask;
use Illuminate\Console\OutputStyle;
use Illuminate\Support\Str;
use function array_values;
use function class_basename;
use function is_bool;
use function str_replace;

/**
 * This service manages the build tasks that are called before and after the site has been compiled using the build command.
 *
 * It is registered as a singleton in the Laravel service container.
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
        $this->registerTasks(Config::getArray('hyde.build_tasks', []));

        $this->registerTasks($this->findTasksInAppDirectory());
    }

    /** @return array<class-string<\Hyde\Framework\Features\BuildTasks\BuildTask>> */
    public function getTasks(): array
    {
        return array_values($this->buildTasks);
    }

    public function runTasks(): void
    {
        foreach ($this->getTasks() as $task) {
            $this->run($task);
        }
    }

    /** @param  class-string<\Hyde\Framework\Features\BuildTasks\BuildTask>  $class */
    public function registerTask(string $class): void
    {
        $this->buildTasks[$this->makeTaskIdentifier($class)] = $class;
    }

    public function registerIf(string $task, callable|bool $condition): void
    {
        if (is_bool($condition) ? $condition : $condition()) {
            $this->registerTask($task);
        }
    }

    public function setOutput(?OutputStyle $output): void
    {
        $this->output = $output;
    }

    protected function findTasksInAppDirectory(): array
    {
        return Filesystem::smartGlob('app/Actions/*BuildTask.php')->map(function (string $file): string {
            return static::pathToClassName($file);
        })->toArray();
    }

    protected function makeTaskIdentifier(string $class): string
    {
        // If a user-land task is registered with the same class name (excluding namespaces) as a framework task,
        // this will allow the user-land task to override the framework task, making them easy to swap out.

        return Str::kebab(class_basename($class));
    }

    protected function run(string $task): void
    {
        $this->runTask(new $task($this->output));
    }

    protected function runTask(BuildTask $task): void
    {
        $task->run();
    }

    protected function registerTasks(array $tasks): void
    {
        foreach ($tasks as $task) {
            $this->registerTask($task);
        }
    }

    protected static function pathToClassName(string $file): string
    {
        return str_replace(['app', '.php', '/'], ['App', '', '\\'], $file);
    }
}
