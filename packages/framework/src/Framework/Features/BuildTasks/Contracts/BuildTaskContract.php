<?php

declare(strict_types=1);

namespace Hyde\Framework\Features\BuildTasks\Contracts;

use Illuminate\Console\OutputStyle;

/**
 * @deprecated
 */
interface BuildTaskContract
{
    public function __construct(?OutputStyle $output = null);

    public function run(): void;

    public function then(): void;

    public function handle(): ?int;

    public function getDescription(): string;

    public function getExecutionTime(): string;
}
