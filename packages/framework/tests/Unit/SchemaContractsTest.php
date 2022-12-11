<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit;

use Hyde\Markdown\Contracts\FrontMatter\BlogPostSchema;
use Hyde\Markdown\Contracts\FrontMatter\DocumentationPageSchema;
use Hyde\Markdown\Contracts\FrontMatter\PageSchema;
use Hyde\Markdown\Contracts\FrontMatter\SubSchemas\NavigationSchema;
use Hyde\Testing\TestCase;

/**
 * A state test to ensure the schemas can't be changed without breaking the tests.
 * This requires contributors to consider the impact of their changes as schema changes are rarely backwards compatible.
 *
 * @see \Hyde\Markdown\Contracts\FrontMatter\PageSchema
 * @see \Hyde\Markdown\Contracts\FrontMatter\BlogPostSchema
 * @see \Hyde\Markdown\Contracts\FrontMatter\DocumentationPageSchema
 */
class SchemaContractsTest extends TestCase
{
    public function testSchemasAreNotAccidentallyChanged()
    {
        $this->assertEquals([
            'title'         => 'string',
            'navigation'    => 'array|navigation',
            'canonicalUrl'  => 'string|url',
        ], PageSchema::PAGE_SCHEMA);

        $this->assertEquals([
            'label'     => 'string',
            'group'     => 'string',
            'hidden'    => 'bool',
            'priority'  => 'int',
        ], NavigationSchema::NAVIGATION_SCHEMA);

        $this->assertEquals([
            'title'        => 'string',
            'description'  => 'string',
            'category'     => 'string',
            'date'         => 'string',
            'author'       => 'string|array|author',
            'image'        => 'string|array|featured_image',
        ], BlogPostSchema::MARKDOWN_POST_SCHEMA);

        $this->assertEquals([
            'name'      => 'string',
            'username'  => 'string',
            'website'   => 'string|url',
        ], BlogPostSchema::AUTHOR_SCHEMA);

        $this->assertEquals([
            'path'           => 'string',
            'url'            => 'string',
            'description'    => 'string',
            'title'          => 'string',
            'copyright'      => 'string',
            'license'        => 'string',
            'licenseUrl'     => 'string',
            'author'         => 'string',
            'attributionUrl' => 'string',
        ], BlogPostSchema::FEATURED_IMAGE_SCHEMA);
    }
}
