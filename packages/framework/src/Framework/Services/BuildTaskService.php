<?php

declare(strict_types=1);

namespace Hyde\Framework\Services;

use Hyde\Framework\Features\BuildTasks\BuildTask;
use Hyde\Framework\Features\BuildTasks\PostBuildTasks\GenerateBuildManifest;
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
    /**
     * Information for package developers: This offers a hook for packages to add custom build tasks.
     * Make sure to add the fully qualified class name to the array and doing so by merging the array, not overwriting it.
     *
     * @var array<class-string<\Hyde\Framework\Features\BuildTasks\BuildTask>>
     *
     * @deprecated This should not be called directly as it will be made non-static, use the registerTask method instead.
     */
    protected static array $legacyPostBuildTasks = [];

    protected ?OutputStyle $output = null;

    public function __construct()
    {
        //
    }

    public function runPostBuildTasks(): void
    {
        foreach ($this->getPostBuildTasks() as $task) {
            $this->run($task);
        }

        $this->runIf(GenerateBuildManifest::class, config('hyde.generate_build_manifest', true));
    }

    /** @return array<class-string<\Hyde\Framework\Features\BuildTasks\BuildTask>> */
    public function getPostBuildTasks(): array
    {
        return array_unique(
            array_merge(
                config('hyde.post_build_tasks', []),
                static::findTasksInAppDirectory(),
                static::$legacyPostBuildTasks
            )
        );
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

    public function run(string $task): static
    {
        $this->runTask(new $task($this->output));

        return $this;
    }

    /** @deprecated Conditions should be evaluated when registering tasks */
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

    public function setOutput(?OutputStyle $output): static
    {
        $this->output = $output;

        return $this;
    }

    /** @param  class-string<\Hyde\Framework\Features\BuildTasks\BuildTask>  $class */
    public function addPostBuildTask(string $class): static
    {
        // self::$legacyPostBuildTasks[$this->makeTaskIdentifier($class)] = $class;
        self::$legacyPostBuildTasks[] = $class;

        return $this;
    }

    protected function makeTaskIdentifier(string $class): string
    {
        return Str::kebab(class_basename($class));
    }
}
