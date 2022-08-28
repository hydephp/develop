<?php

namespace Hyde\Framework\Services;

use Hyde\Framework\Contracts\BuildTaskContract;
use Illuminate\Console\OutputStyle;

/**
 * @see \Hyde\Framework\Testing\Feature\Services\BuildHookServiceTest
 */
class BuildHookService
{
    /**
     * Offers a hook for packages to add custom build tasks.
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

    /**
     * @todo #439 Automatically discover files in the app directory?
     */
    public function getPostBuildTasks(): array
    {
        return array_unique(
            array_merge(
                config('hyde.post_build_tasks', []),
                static::$postBuildTasks
            )
        );
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
