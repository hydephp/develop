<?php

declare(strict_types=1);

/**
 * @internal
 */

use Hyde\Pages\Concerns\HydePage;

require_once __DIR__.'/../../../vendor/autoload.php';

$class = HydePage::class;
$instanceVariableName = '$page';

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

// Save the documentation to a file
file_put_contents('api-docs.md', implode("\n", $output));

// Helpers

function documentMethod(ReflectionMethod $method, array &$output): void
{
    $template = <<<'MARKDOWN'
    #### `{{ $methodName }}()`
    
    {{ $description }}
    
    ```php
    // torchlight! {"lineNumbers": false}
    {{ $signature }}({{ $argList }}): {{ $returnType }}
    ```

    MARKDOWN;

    $staticSignatureTemplate = '{{ $className }}::{{ $methodName }}';
    $instanceSignatureTemplate = '{{ $instanceVariableName }}->{{ $methodName }}';

    $signatureTemplate = $method->isStatic() ? $staticSignatureTemplate : $instanceSignatureTemplate;

    if ($method->getName() === '__construct') {
        $signatureTemplate = '{{ $instanceVariableName }} = new {{ $className }}';
    }

    $methodName = $method->getName();
    $docComment = parsePHPDocs($method->getDocComment() ?: '');
    $description = $docComment['description'];

    global $class;
    $className = class_basename($class);

    $parameters = array_map(function (ReflectionParameter $parameter) {
        $name = '$'.$parameter->getName();
        if ($parameter->getType()) {
            if ($parameter->getType() instanceof ReflectionUnionType) {
                $type = implode('|', array_map(function (ReflectionNamedType $type) {
                    return $type->getName();
                }, $parameter->getType()->getTypes()));
            } else {
                $type = $parameter->getType()->getName();
            }
        } else {
            $type = 'mixed';
        }

        return trim($type.' '.$name);
    }, $method->getParameters());
    $returnType = $method->getReturnType() ? $method->getReturnType()->getName() : 'void';

    // If higher specificity return type is provided in docblock, use that instead
    if (isset($docComment['properties']['return'])) {
        $returnType = $docComment['properties']['return'];
    }

    // Map docblock params
    if (isset($docComment['properties']['params'])) {
        $newParams = array_map(function (string $param) {
            $param = str_replace('  ', ' ', trim($param));
            $param = explode(' ', $param);
            $type = array_shift($param);
            $name = array_pop($param);

            return trim($type.' '.$name);
        }, $docComment['properties']['params']);
    }
    // If higher specificity argument types are provided in docblock, merge them with the actual types
    if (isset($newParams)) {
        foreach ($newParams as $index => $newParam) {
            if (isset($parameters[$index])) {
                $parameters[$index] = $newParam;
            }
        }
    }

    $argList = implode(', ', $parameters);

    global $instanceVariableName;
    $signature = str_replace(
        ['{{ $instanceVariableName }}', '{{ $methodName }}', '{{ $className }}'],
        [$instanceVariableName, $methodName, $className],
        $signatureTemplate
    );

    $replacements = [
        '{{ $signature }}' => $signature,
        '{{ $methodName }}' => e($methodName),
        '{{ $description }}' => e($description),
        '{{ $className }}' => e($className),
        '{{ $argList }}' => e($argList),
        '{{ $returnType }}' => ($returnType),
    ];
    $markdown = str_replace(array_keys($replacements), array_values($replacements), $template);

    // Throws
    if (isset($docComment['properties']['throws'])) {
        $markdown .= "\n";
        foreach ($docComment['properties']['throws'] as $throw) {
            $markdown .= e("- **Throws:** $throw\n");
        }
    }

    $output[] = $markdown;
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
            // If property allows multiple we add to subarray
            if ($propertyName === 'return') {
                $properties[$propertyName] = $propertyValue;
            } else {
                $name = str_ends_with($propertyName, 's') ? $propertyName : $propertyName.'s';
                $properties[$name][] = $propertyValue;
            }
        } else {
            $shouldAddNewline = empty($line);
            $description .= ($shouldAddNewline ? "\n\n" : '').ltrim($line.' ');
        }
    }

    return [
        'description' => trim($description) ?: 'No description provided.',
        'properties' => $properties,
    ];
}
