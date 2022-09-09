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

    public static function json(bool $pretty = true): string
    {
        return self::jsonEncode(self::all(), $pretty);
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

    public static function getPageJson(bool $pretty = true): string
    {
        return self::jsonEncode(self::getPageArray(), $pretty);
    }

    public static function getBlogPostJson(bool $pretty = true): string
    {
        return self::jsonEncode(self::getBlogPostArray(), $pretty);
    }

    public static function getDocumentationPageJson(bool $pretty = true): string
    {
        return self::jsonEncode(self::getDocumentationPageArray(), $pretty);
    }

    protected static function jsonEncode(array $data, bool $pretty = true): string
    {
        return json_encode($data, $pretty ? JSON_PRETTY_PRINT : 0);
    }
}
