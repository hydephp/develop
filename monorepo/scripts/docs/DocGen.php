<?php

declare(strict_types=1);

/**
 * @internal
 */
require_once __DIR__.'/../../../vendor/autoload.php';

$class = \Hyde\Pages\Concerns\HydePage::class;

$reflection = new \ReflectionClass($class);

$methods = $reflection->getMethods(\ReflectionMethod::IS_PUBLIC);
