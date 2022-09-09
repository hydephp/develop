<?php

namespace Hyde\Framework\Concerns\FrontMatter\Schemas;

/**
 * Class representation of all the available schema traits with helpers to access them.
 *
 * All front matter properties are always optional in HydePHP.
 *
 * @see \Hyde\Framework\Concerns\FrontMatter\Schemas\PageSchema
 * @see \Hyde\Framework\Concerns\FrontMatter\Schemas\BlogPostSchema
 * @see \Hyde\Framework\Concerns\FrontMatter\Schemas\DocumentationPageSchema
 */
final class Schemas
{
    public static function all(): array
    {
        return [
            'PageSchema' => self::get(PageSchema::class),
            'BlogPostSchema' => self::get(BlogPostSchema::class),
            'DocumentationPageSchema' => self::get(DocumentationPageSchema::class),
        ];
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

    public static function get(string $schema): array
    {
        return match ($schema) {
            PageSchema::class => self::getPageArray(),
            BlogPostSchema::class => self::getBlogPostArray(),
            DocumentationPageSchema::class => self::getDocumentationPageArray(),
            default => throw new \Exception("Schema $schema does not exist."),
        };
    }
}
