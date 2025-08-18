<?php

use Rector\Php80\Rector\Class_\AnnotationToAttributeRector;
use Rector\Config\RectorConfig;
use Rector\PHPUnit\Set\PHPUnitSetList;

return static function (RectorConfig $rectorConfig): void {
    // Limit Rector to existing test directories
    $potentialTestPaths = [
        __DIR__.'/tests',
        __DIR__.'/packages/framework/tests',
        __DIR__.'/packages/publications/tests',
        __DIR__.'/packages/hyde/tests',
        __DIR__.'/packages/realtime-compiler/tests',
    ];
    $rectorConfig->paths(array_values(array_filter($potentialTestPaths, 'is_dir')));

    // Apply PHPUnit migrations and improvements
    $rectorConfig->sets([
        PHPUnitSetList::PHPUNIT_100,
        PHPUnitSetList::PHPUNIT_110,
        PHPUnitSetList::PHPUNIT_CODE_QUALITY,
    ]);

    // Skip unstable rules for specific files that error out
    $rectorConfig->skip([
        \Rector\Renaming\Rector\MethodCall\RenameMethodRector::class => [
            __DIR__.'/packages/framework/tests/Unit/BuildTaskServiceUnitTest.php',
        ],
        \Rector\PHPUnit\CodeQuality\Rector\MethodCall\FlipAssertRector::class => [
            __DIR__.'/packages/framework/tests/Feature/AutomaticNavigationConfigurationsTest.php',
        ],
    ]);

    $openApiAnnotationsRaw = [
        'AdditionalProperties',
        'Attachable',
        'Components',
        'Contact',
        'Delete',
        'Discriminator',
        'Examples',
        'ExternalDocumentation',
        'Flow',
        'Get',
        'Head',
        'Header',
        'Info',
        'Items',
        'JsonContent',
        'License',
        'Link',
        'MediaType',
        'OpenApi',
        'Operation',
        'Options',
        'Parameter',
        'Patch',
        'PatchItem',
        'PathParameter',
        'Post',
        'Property',
        'Put',
        'RequestBody',
        'Response',
        'Schema',
        'SecurityScheme',
        'Server',
        'ServerVariable',
        'Tag',
        'Trace',
        'Xml',
        'XmlContent',
    ];

    $openApiAnnotations = [];
    foreach ($openApiAnnotationsRaw as $className) {
        $openApiAnnotations[] = new \Rector\Php80\ValueObject\AnnotationToAttribute(
            'OpenApi\\Annotations\\'.$className,
            'OpenApi\\Attributes\\'.$className
        );
    }

    $rectorConfig->ruleWithConfiguration(AnnotationToAttributeRector::class, $openApiAnnotations);
};
