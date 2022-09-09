<?php

namespace Hyde\Framework\Concerns\FrontMatter\Schemas;

/**
 * Class representation of all the available schema traits with helpers to access them.
 *
 * All front matter properties are always optional in HydePHP.
 */
final class Schemas
{
    public static function all(): array
    {
        return [
            'PageSchema' => self::getPageArray(),
            'BlogPostSchema' => self::getBlogPostArray(),
            'DocumentationPageSchema' => self::getDocumentationPageArray(),
        ];
    }

    public static function json(): string
    {
        return json_encode(self::all());
    }

    public static function getPageArray(): array
    {
        return [
            'title' => 'string',
            'navigation' => 'array',
            'canonicalUrl' => 'string',
        ];
    }

    public static function getBlogPostArray(): array
    {
        return [
            'title' => 'string',
            'description' => 'string',
            'category' => 'string',
            'date' => 'string',
            'author' => 'string|array',
            'image' => 'string|array',
        ];
    }

    public static function getDocumentationPageArray(): array
    {
        return [
            'category' => 'string',
            'label' => 'string',
            'hidden' => 'bool',
            'priority' => 'int',
        ];
    }
}
