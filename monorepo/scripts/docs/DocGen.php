<?php

declare(strict_types=1);

/**
 * @internal
 */

use Hyde\Pages\Concerns\HydePage;

require_once __DIR__.'/../../../vendor/autoload.php';

$class = HydePage::class;

$reflection = new ReflectionClass($class);

$methods = $reflection->getMethods(ReflectionMethod::IS_PUBLIC);
$fileName = (new ReflectionClass($class))->getFileName();

// Remove methods defined in traits or parent classes
$methods = array_filter($methods, function (ReflectionMethod $method) use ($fileName) {
    return $method->getFileName() === $fileName;
});

// Split methods into static and non-static

$staticMethods = array_filter($methods, function (ReflectionMethod $method) {
    return $method->isStatic();
});

$instanceMethods = array_filter($methods, function (ReflectionMethod $method) {
    return ! $method->isStatic();
});

$output = [];

// Generate static methods
foreach ($staticMethods as $method) {
    documentMethod($method, $output);
}

// Generate instance methods
foreach ($instanceMethods as $method) {
    documentMethod($method, $output);
}

// Output the documentation
echo implode("\n", $output);

function documentMethod(ReflectionMethod $method, array &$output): void
{
    //
}
