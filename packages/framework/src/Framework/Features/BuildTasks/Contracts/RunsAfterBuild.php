<?php

declare(strict_types=1);

namespace Hyde\Framework\Features\BuildTasks\Contracts;

/**
 * Indicates that the build task should be run after the static site build.
 *
 * @deprecated Extend the PostBuildTask class instead.
 */
interface RunsAfterBuild
{
    //
}
