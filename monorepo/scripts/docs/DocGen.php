<?php

declare(strict_types=1);

/**
 * @internal
 */

use Hyde\Pages\Concerns\HydePage;
use Illuminate\Support\Str;

// Check if --lint flag is present
if (in_array('--lint', $argv, true)) {
    echo "\033[32mLinting...\033[0m";

    // Rerun the script without the --lint flag
    $output = shell_exec('php '.__FILE__);

    $dirty = str_contains($output, 'Convents saved');

    if ($dirty) {
        echo " \033[31mDocumentation is not up to date!\033[0m\n";
        exit(1);
    }

    echo " \033[32mDocumentation is up to date!\033[0m\n";
    exit(0);
}

require_once __DIR__.'/../../../vendor/autoload.php';

echo "\033[32mHydePHP method DocGen\033[0m\n\n";

$basePath = realpath(__DIR__.'/../../../docs/_data/partials/hyde-pages-api');

$matrix = [
    [
        'class' => HydePage::class,
        'instanceVariableName' => '$page',
        'outputFile' => "$basePath/hyde-page-methods.md",

    ],
    [
        'class' => \Hyde\Pages\Concerns\BaseMarkdownPage::class,
        'instanceVariableName' => '$page',
        'outputFile' => "$basePath/base-markdown-page-methods.md",
    ],
    [
        'class' => \Hyde\Framework\Concerns\InteractsWithFrontMatter::class,
        'instanceVariableName' => '$page',
        'outputFile' => "$basePath/interacts-with-front-matter-methods.md",
    ],
    [
        'class' => \Hyde\Pages\InMemoryPage::class,
        'instanceVariableName' => '$page',
        'outputFile' => "$basePath/in-memory-page-methods.md",
    ],
    [
        'class' => \Hyde\Pages\BladePage::class,
        'instanceVariableName' => '$page',
        'outputFile' => "$basePath/blade-page-methods.md",
    ],
    [
        'class' => \Hyde\Pages\MarkdownPage::class,
        'instanceVariableName' => '$page',
        'outputFile' => "$basePath/markdown-page-methods.md",
    ],
    [
        'class' => \Hyde\Pages\MarkdownPost::class,
        'instanceVariableName' => '$page',
        'outputFile' => "$basePath/markdown-post-methods.md",
    ],
    [
        'class' => \Hyde\Pages\DocumentationPage::class,
        'instanceVariableName' => '$page',
        'outputFile' => "$basePath/documentation-page-methods.md",
    ],
    [
        'class' => \Hyde\Pages\HtmlPage::class,
        'instanceVariableName' => '$page',
        'outputFile' => "$basePath/html-page-methods.md",
    ],
    [
        'class' => \Hyde\Foundation\HydeKernel::class,
        'instanceVariableName' => '$hyde',
        'outputFile' => "$basePath/hyde-kernel-base-methods.md",
        'facadeName' => 'Hyde',
    ],
    // HandlesFoundationCollections
    [
        'class' => \Hyde\Foundation\Concerns\HandlesFoundationCollections::class,
        'instanceVariableName' => '$hyde',
        'outputFile' => "$basePath/hyde-kernel-foundation-methods.md",
        'facadeName' => 'Hyde',
    ],
    // ImplementsStringHelpers
    [
        'class' => \Hyde\Foundation\Concerns\ImplementsStringHelpers::class,
        'instanceVariableName' => '$hyde',
        'outputFile' => "$basePath/hyde-kernel-string-methods.md",
        'facadeName' => 'Hyde',
    ],
    // ForwardsHyperlinks
    [
        'class' => \Hyde\Foundation\Concerns\ForwardsHyperlinks::class,
        'instanceVariableName' => '$hyde',
        'outputFile' => "$basePath/hyde-kernel-hyperlink-methods.md",
        'facadeName' => 'Hyde',
    ],
    // ForwardsFilesystem
    [
        'class' => \Hyde\Foundation\Concerns\ForwardsFilesystem::class,
        'instanceVariableName' => '$hyde',
        'outputFile' => "$basePath/hyde-kernel-filesystem-methods.md",
        'facadeName' => 'Hyde',
    ],
    // ManagesHydeKernel
    [
        'class' => \Hyde\Foundation\Concerns\ManagesHydeKernel::class,
        'instanceVariableName' => '$hyde',
        'outputFile' => "$basePath/hyde-kernel-kernel-methods.md",
        'facadeName' => 'Hyde',
    ],
    // ManagesExtensions
    [
        'class' => \Hyde\Foundation\Concerns\ManagesExtensions::class,
        'instanceVariableName' => '$hyde',
        'outputFile' => "$basePath/hyde-kernel-extensions-methods.md",
        'facadeName' => 'Hyde',
    ],
    // ManagesViewData
    [
        'class' => \Hyde\Foundation\Concerns\ManagesViewData::class,
        'instanceVariableName' => '$hyde',
        'outputFile' => "$basePath/hyde-kernel-view-methods.md",
        'facadeName' => 'Hyde',
    ],
    // BootsHydeKernel
    [
        'class' => \Hyde\Foundation\Concerns\BootsHydeKernel::class,
        'instanceVariableName' => '$hyde',
        'outputFile' => "$basePath/hyde-kernel-boot-methods.md",
        'facadeName' => 'Hyde',
    ],
];
$timeStart = microtime(true);

foreach ($matrix as $key => $options) {
    if ($key > 0) {
        echo "\n";
    }
    generate($options);
}

// Update the HydeKernel page
(function (): void {
    echo "\n\033[33mUpdating the HydeKernel page...\033[0m";

    $pages = [
        'hyde-kernel-base-methods',
        'hyde-kernel-foundation-methods',
        'hyde-kernel-string-methods',
        'hyde-kernel-hyperlink-methods',
        'hyde-kernel-filesystem-methods',
        'hyde-kernel-kernel-methods',
        'hyde-kernel-extensions-methods',
        'hyde-kernel-view-methods',
        'hyde-kernel-boot-methods',
    ];

    $page = 'docs/architecture-concepts/the-hydekernel.md';

    // Replace everything between <!-- Start generated docs for the HydeKernel --> and <!-- End generated docs for the HydeKernel -->
    // With the concatenated content of the partials

    $startMarker = '<!-- Start generated docs for the HydeKernel -->';
    $endMarker = '<!-- End generated docs for the HydeKernel -->';

    $rootPath = realpath(__DIR__.'/../../../');

    $content = '';
    foreach ($pages as $partial) {
        $content .= trim(file_get_contents($rootPath.'/docs/_data/partials/hyde-pages-api/'.$partial.'.md'))."\n\n";
    }

    $file = file_get_contents($page);
    $file = preg_replace('/<!-- Start generated docs for the HydeKernel -->.*<!-- End generated docs for the HydeKernel -->/s', $startMarker."\n\n".$content.$endMarker, $file);

    file_put_contents($page, $file);

    echo " \033[37mDone!\033[0m\n";
})();

// Assemble end time in milliseconds
$timeEnd = microtime(true);
$time = number_format(($timeEnd - $timeStart) * 1000, 2);
echo "\n\033[32mAll done in $time ms!\n\033[0m";

// Helpers

function generate(array $options): void
{
    $timeStart = microtime(true);

    $class = $options['class'];
    $instanceVariableName = $options['instanceVariableName'];
    $outputFile = $options['outputFile'];
    $facadeName = $options['facadeName'] ?? null; // If the class has a facade we use that instead of instance variable names

    echo "\033[33mGenerating documentation for $class...\033[0m";

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
        documentMethod($method, $output, $class, $instanceVariableName, $facadeName);
    }

    // Generate instance methods
    foreach ($instanceMethods as $method) {
        documentMethod($method, $output, $class, $instanceVariableName, $facadeName);
    }

    // Assemble end time in milliseconds
    $timeEnd = microtime(true);
    $time = number_format(($timeEnd - $timeStart) * 1000, 2);
    $metadata = sprintf('Generated by HydePHP DocGen script at %s in %sms', date('Y-m-d H:i:s'), $time);

    // Join the output
    $text = implode("\n", $output);
    $startMarker = '<!-- Start generated docs for '.$class.' -->';
    $metadataMarker = "<!-- $metadata -->";
    $endMarker = '<!-- End generated docs for '.$class.' -->';
    $classKebabName = Str::kebab(class_basename($class));
    $baseName = basename($outputFile, '.md');
    $text = "<section id=\"$baseName\">\n\n$startMarker\n$metadataMarker\n\n$text\n$endMarker\n\n</section>\n";

    // Run any post-processing
    $text = postProcess($text);

    // Output the documentation
    // echo $text;

    // Check if any changes were made compared to the existing file (excluding metadata markers)
    if (file_exists($outputFile)) {
        $existingFile = file_get_contents($outputFile);
        $existingFile = preg_replace('/<!-- Generated by HydePHP DocGen script at .* in .*ms -->/', '', $existingFile);
        $compareText = preg_replace('/<!-- Generated by HydePHP DocGen script at .* in .*ms -->/', '', $text);
        if ($existingFile === $compareText) {
            echo "\n\033[37mNo changes made to $outputFile\033[0m\n";

            return;
        }
    }

    // Save the documentation to a file
    file_put_contents($outputFile, $text);

    echo "\n\033[0m Convents saved to ".realpath($outputFile)."\n";
}

function documentMethod(ReflectionMethod $method, array &$output, string $class, string $instanceVariableName, ?string $facadeName = null): void
{
    $template = <<<'MARKDOWN'
    #### `{{ $methodName }}()`

    {{ $description }}

    ```php
    {{ $signature }}({{ $argList }}): {{ $returnType }}
    ```

    MARKDOWN;

    $staticSignatureTemplate = '{{ $className }}::{{ $methodName }}';
    $instanceSignatureTemplate = '{{ $instanceVariableName }}->{{ $methodName }}';
    $facadeSignatureTemplate = '{{ $facadeName }}::{{ $methodName }}';

    $signatureTemplate = $method->isStatic() ? $staticSignatureTemplate : $instanceSignatureTemplate;

    if ($facadeName !== null) {
        $signatureTemplate = $facadeSignatureTemplate;
    }

    if ($method->getName() === '__construct') {
        $signatureTemplate = '{{ $instanceVariableName }} = new {{ $className }}';
    }

    $methodName = $method->getName();

    // If method uses inheritdoc, use the parent method's docblock
    if ($method->getDocComment() !== false && str_contains(strtolower($method->getDocComment()), '@inheritdoc')) {
        try {
            $parentMethod = $method->getPrototype();
            $docComment = $parentMethod->getDocComment();
        } catch (ReflectionException $e) {
            // if method is for constructor, getPrototype() will throw an exception,
            // so we check if exception is for constructor and if so, we use the parent class's constructor
            if ($method->getName() === '__construct') {
                $parentClass = $method->getDeclaringClass()->getParentClass();
                $parentMethod = $parentClass->getMethod('__construct');
                $docComment = $parentMethod->getDocComment();
            } else {
                $docComment = null;
            }
        }
    } else {
        $docComment = $method->getDocComment();
    }

    $PHPDocs = parsePHPDocs($docComment ?: '');
    $description = $PHPDocs['description'];

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

    // If return is union type
    if ($method->getReturnType() instanceof ReflectionUnionType) {
        $returnType = implode('|', array_map(function (ReflectionNamedType $type) {
            return $type->getName();
        }, $method->getReturnType()->getTypes()));
    } else {
        $returnType = $method->getReturnType() ? $method->getReturnType()->getName() : 'void';
    }

    // If higher specificity return type is provided in docblock, use that instead
    if (isset($PHPDocs['properties']['return'])) {
        $returnValue = $PHPDocs['properties']['return'];
        // If there is a description, put it in a comment
        if (str_contains($returnValue, ' ')) {
            $exploded = explode(' ', $returnValue, 2);
            // If is not generic
            if (! str_contains($exploded[0], '<')) {
                $type = $exploded[0];
                $comment = ' // '.$exploded[1];
                $returnValue = $type;
            } else {
                $comment = null;
            }
        } else {
            $comment = null;
        }
        $returnType = $returnValue.($comment ?? '');
    }

    $parameterDocs = [];
    // Map docblock params
    if (isset($PHPDocs['properties']['params'])) {
        $newParams = array_map(function (string $param) use (&$parameterDocs) {
            $param = str_replace('  ', ' ', trim($param));
            $comment = $param;
            $param = explode(' ', $param, 3);
            $type = $param[0];
            $name = $param[1];
            if (isset($param[2])) {
                $parameterDocs[$type] = $comment;
            }

            return trim($type.' '.$name);
        }, $PHPDocs['properties']['params']);
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

    $before = null;
    $beforeSignature = null;
    if ($parameterDocs) {
        if (count($parameterDocs) > 1) {
            foreach ($parameterDocs as $type => $param) {
                $name = explode(' ', $param, 3)[1];
                $desc = explode(' ', $param, 3)[2];
                $before .= "- **Parameter $name:** $desc \n";
            }
        } else {
            $param = array_values($parameterDocs)[0];
            $beforeSignature = "/** @param $param */";
        }
    }

    $signature = ($beforeSignature ? $beforeSignature."\n" : '').str_replace(
        ['{{ $instanceVariableName }}', '{{ $methodName }}', '{{ $className }}'],
        [$instanceVariableName, $methodName, $className],
        $signatureTemplate
    );

    $description = $description.($before ? "\n".$before : '');
    $replacements = [
        '{{ $signature }}' => $signature,
        '{{ $methodName }}' => e($methodName),
        '{{ $description }}' => e($description),
        '{{ $className }}' => e($className),
        '{{ $argList }}' => e($argList),
        '{{ $returnType }}' => $returnType,
        '{{ $facadeName }}' => $facadeName,
    ];
    $markdown = str_replace(array_keys($replacements), array_values($replacements), $template);

    // Throws
    if (isset($PHPDocs['properties']['throws'])) {
        $markdown .= "\n";
        foreach ($PHPDocs['properties']['throws'] as $throw) {
            $markdown .= e("- **Throws:** $throw\n");
        }
    }

    // Debug breakpoint
    if (str_contains($markdown, 'foo')) {
        // dd($markdown);
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

function postProcess(string $text): string
{
    // Unescape escaped code that will be escaped again
    $replace = ['`&lt;' => '`<', '&gt;`' => '>`'];
    $text = str_replace(array_keys($replace), array_values($replace), $text);

    // Trim trailing whitespace
    $text = implode("\n", array_map(function (string $line) {
        return rtrim($line);
    }, explode("\n", $text)));

    return $text;
}
