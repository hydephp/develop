<?php

namespace Hyde\Framework\Testing\Feature;

use Hyde\Framework\Concerns\FrontMatter\Schemas\BlogPostSchema;
use Hyde\Framework\Concerns\FrontMatter\Schemas\DocumentationPageSchema;
use Hyde\Framework\Concerns\FrontMatter\Schemas\PageSchema;
use Hyde\Framework\Concerns\FrontMatter\Schemas\Schemas;
use Hyde\Testing\TestCase;

/**
 * @covers \Hyde\Framework\Concerns\FrontMatter\Schemas\Schemas
 */
class SchemasClassTest extends TestCase
{
    public function testAll()
    {
        $this->assertEquals([
            'PageSchema' => Schemas::getPageArray(),
            'BlogPostSchema' => Schemas::getBlogPostArray(),
            'DocumentationPageSchema' => Schemas::getDocumentationPageArray(),
        ], Schemas::all());
    }

    public function testGetPageArray()
    {
        $this->assertEquals([
            'title' => 'string',
            'navigation' => 'array',
            'canonicalUrl' => 'string',
        ], Schemas::getPageArray());
    }

    public function testGetBlogPostArray()
    {
        $this->assertEquals([
            'title' => 'string',
            'description' => 'string',
            'category' => 'string',
            'date' => 'string',
            'author' => 'string|array',
            'image' => 'string|array',
        ], Schemas::getBlogPostArray());
    }

    public function testGetDocumentationPageArray()
    {
        $this->assertEquals([
            'category' => 'string',
            'label' => 'string',
            'hidden' => 'bool',
            'priority' => 'int',
        ], Schemas::getDocumentationPageArray());
    }

    public function testGet()
    {
        $this->assertEquals(Schemas::getPageArray(), Schemas::get(PageSchema::class));
        $this->assertEquals(Schemas::getBlogPostArray(), Schemas::get(BlogPostSchema::class));
        $this->assertEquals(Schemas::getDocumentationPageArray(), Schemas::get(DocumentationPageSchema::class));
    }
}
