<?php

declare(strict_types=1);

namespace Hyde\Framework\Services;

use Hyde\Facades\Config;
use Hyde\Facades\Filesystem;
use Hyde\Framework\Features\BuildTasks\BuildTask;
use Hyde\Framework\Features\BuildTasks\Contracts\RunsAfterBuild;
use Hyde\Framework\Features\BuildTasks\Contracts\RunsBeforeBuild;
use Illuminate\Console\OutputStyle;
use Illuminate\Support\Str;
use InvalidArgumentException;
use function array_map;
use function array_values;
use function class_basename;
use function is_bool;
use function is_string;
use function str_replace;

/**
 * This service manages the build tasks that are called before and after the site is compiled using the build command.
 *
 * The class is registered as a singleton in the Laravel service container and is run by the build command.
 * Build Tasks can be registered programmatically, through the config, and through autodiscovery.
 * The service determines when to run a task depending on the interface it implements.
 *
 * @see \Hyde\Framework\Testing\Feature\Services\BuildTaskServiceTest
 * @see \Hyde\Framework\Testing\Unit\BuildTaskServiceUnitTest
 */
class BuildTaskService
{
    /** @var array<string, \Hyde\Framework\Features\BuildTasks\BuildTask> */
    protected array $buildTasks = [];

    protected ?OutputStyle $output = null;

    public function __construct()
    {
        $this->registerTasks(Config::getArray('hyde.build_tasks', []));

        $this->registerTasks($this->findTasksInAppDirectory());
    }

    /** @return array<class-string<\Hyde\Framework\Features\BuildTasks\BuildTask>> */
    public function getRegisteredTasks(): array
    {
        return array_map(function (BuildTask $task): string {
            return $task::class;
        }, array_values($this->buildTasks));
    }

    public function runPreBuildTasks(): void
    {
        foreach ($this->buildTasks as $task) {
            if ($task instanceof RunsBeforeBuild) {
                $task->run($this->output);
            }
        }
    }

    public function runPostBuildTasks(): void
    {
        foreach ($this->buildTasks as $task) {
            if ($task instanceof RunsAfterBuild) {
                $task->run($this->output);
            }
        }
    }

    /** @param  \Hyde\Framework\Features\BuildTasks\BuildTask|class-string<\Hyde\Framework\Features\BuildTasks\BuildTask>  $task */
    public function registerTask(BuildTask|string $task): void
    {
        $task = is_string($task) ? new $task() : $task;

        if (! $task instanceof BuildTask) {
            throw new InvalidArgumentException('BuildTask ['.$task::class.'] must extend the HydeBuildTask class.');
        }

        if (! ($task instanceof RunsBeforeBuild || $task instanceof RunsAfterBuild)) {
            throw new InvalidArgumentException('BuildTask ['.$task::class.'] must implement either the RunsBeforeBuild or RunsAfterBuild interface.');
        }

        $this->buildTasks[$this->makeTaskIdentifier($task)] = $task;
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

    protected function makeTaskIdentifier(BuildTask $class): string
    {
        // If a user-land task is registered with the same class name (excluding namespaces) as a framework task,
        // this will allow the user-land task to override the framework task, making them easy to swap out.

        return Str::kebab(class_basename($class));
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
