<?php

declare(strict_types=1);

/**
 * @internal
 */
require_once __DIR__.'/../../../vendor/autoload.php';

$class = \Hyde\Pages\Concerns\HydePage::class;

$reflection = new \ReflectionClass($class);

$methods = $reflection->getMethods(\ReflectionMethod::IS_PUBLIC);
$fileName = (new \ReflectionClass($class))->getFileName();

// Remove methods defined in traits or parent classes
$methods = array_filter($methods, function (\ReflectionMethod $method) use ($fileName) {
    return $method->getFileName() === $fileName;
});
