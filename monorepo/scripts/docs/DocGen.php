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


// Helpers

function documentMethod(ReflectionMethod $method, array &$output): void
{
    $template = <<<'MARKDOWN'
    #### `{{ $methodName }}()`
    
    {{ $description }}
    
    ```php
    {{ $class }}::{{ $methodName }}({{ $argList }}): {{ $returnType }}
    ```

    MARKDOWN;

    $methodName = $method->getName();
    $docComment = parsePHPDocs($method->getDocComment() ?: '');
    $description = $docComment['description'];

    $class = $method->getDeclaringClass()->getName();
    $argList = implode(', ', array_map(function (ReflectionParameter $parameter) {
        $name = '$'.$parameter->getName();
        return $name;
    }, $method->getParameters()));
    $returnType = $method->getReturnType() ? $method->getReturnType()->getName() : 'void';

    $output[] = str_replace(
        ['{{ $methodName }}', '{{ $description }}', '{{ $class }}', '{{ $argList }}', '{{ $returnType }}'],
        [$methodName, $description, $class, $argList, $returnType],
        $template
    );
}

function parsePHPDocs(string $comment): array
{
    // Normalize
    $comment = array_map(function (string $line) {
        return trim($line, " \t*/");
    }, explode("\n", $comment));

    $description = '';
    $properties = [];

    // Parse
    foreach ($comment as $line) {
        if (str_starts_with($line, '@')) {
            $propertyName = substr($line, 1, strpos($line, ' ') - 1);
            $propertyValue = substr($line, strpos($line, ' ') + 1);
            $properties[$propertyName] = $propertyValue;
        } else {
            $shouldAddNewline = empty($line);
            $description .= ($shouldAddNewline ? "\n\n" : ' ').$line;
        }
    }

    return [
        'description' => trim($description) ?: 'No description provided.',
        'properties' => $properties,
    ];
}
