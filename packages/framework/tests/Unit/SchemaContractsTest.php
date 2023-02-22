<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit;

use Hyde\Markdown\Contracts\FrontMatter\BlogPostSchema;
use Hyde\Markdown\Contracts\FrontMatter\PageSchema;
use Hyde\Markdown\Contracts\FrontMatter\SubSchemas\NavigationSchema;
use PHPUnit\Framework\TestCase;

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
        $this->assertSame([
            'title'         => 'string',
            'canonicalUrl'  => 'string(url)',
            'navigation'    => 'array<navigation>',
        ], PageSchema::PAGE_SCHEMA);

        $this->assertSame([
            'label'     => 'string',
            'group'     => 'string',
            'hidden'    => 'bool',
            'priority'  => 'int',
        ], NavigationSchema::NAVIGATION_SCHEMA);

        $this->assertSame([
            'title'        => 'string',
            'description'  => 'string',
            'category'     => 'string',
            'date'         => 'string',
            'author'       => 'string|array<blog_post.author>',
            'image'        => 'string|array<featured_image>',
        ], BlogPostSchema::MARKDOWN_POST_SCHEMA);

        $this->assertSame([
            'name'      => 'string',
            'username'  => 'string',
            'website'   => 'string(url)',
        ], BlogPostSchema::AUTHOR_SCHEMA);

        $this->assertSame([
            'source'         => 'string',
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
