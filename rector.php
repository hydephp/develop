<?php

use Rector\Php80\Rector\Class_\AnnotationToAttributeRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

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
            'OpenApi\\Annotations\\' . $className,
            'OpenApi\\Attributes\\' . $className
        );
    }

    $services->set(AnnotationToAttributeRector::class)
        ->configure($openApiAnnotations);
};