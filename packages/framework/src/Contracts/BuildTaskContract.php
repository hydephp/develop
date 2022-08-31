<?php

namespace Hyde\Framework\Contracts;

use Illuminate\Console\OutputStyle;

/**
 * @experimental This feature was recently added and may be changed without notice.
 *
 * @since 0.40.0
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
