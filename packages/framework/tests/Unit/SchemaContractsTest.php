<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit;

use Hyde\Markdown\Contracts\FrontMatter\BlogPostSchema;
use Hyde\Markdown\Contracts\FrontMatter\FrontMatterSchema;
use Hyde\Markdown\Contracts\FrontMatter\PageSchema;
use Hyde\Markdown\Contracts\FrontMatter\SubSchemas\AuthorSchema;
use Hyde\Markdown\Contracts\FrontMatter\SubSchemas\FeaturedImageSchema;
use Hyde\Markdown\Contracts\FrontMatter\SubSchemas\NavigationSchema;
use Hyde\Testing\UnitTestCase;
use Illuminate\Support\Str;

use function array_filter;
use function array_map;
use function array_merge;
use function array_values;
use function basename;
use function defined;
use function file_get_contents;
use function glob;
use function is_subclass_of;
use function substr_count;

/**
 * A state test to ensure the schemas can't be changed without breaking the tests.
 * This requires contributors to consider the impact of their changes as schema changes are rarely backwards compatible.
 *
 * @see \Hyde\Markdown\Contracts\FrontMatter\PageSchema
 * @see \Hyde\Markdown\Contracts\FrontMatter\BlogPostSchema
 * @see \Hyde\Markdown\Contracts\FrontMatter\DocumentationPageSchema
 */
class SchemaContractsTest extends UnitTestCase
{
    protected const SCHEMAS = [
        BlogPostSchema::class,
        PageSchema::class,
        AuthorSchema::class,
        FeaturedImageSchema::class,
        NavigationSchema::class,
    ];

    public function testSchemasAreNotAccidentallyChanged()
    {
        $this->assertSame([
            'title'         => 'string',
            'canonicalUrl'  => 'string',
            'navigation'    => NavigationSchema::NAVIGATION_SCHEMA,
        ], PageSchema::PAGE_SCHEMA);

        $this->assertSame([
            'label'     => 'string',
            'priority'  => 'int',
            'hidden'    => 'bool',
            'group'     => 'string',
        ], NavigationSchema::NAVIGATION_SCHEMA);

        $this->assertSame([
            'title'        => 'string',
            'description'  => 'string',
            'category'     => 'string',
            'date'         => 'string',
            'author'       => ['string', AuthorSchema::AUTHOR_SCHEMA],
            'image'        => ['string', FeaturedImageSchema::FEATURED_IMAGE_SCHEMA],
        ], BlogPostSchema::BLOG_POST_SCHEMA);

        $this->assertSame([
            'name'      => 'string',
            'username'  => 'string',
            'website'   => 'string',
        ], AuthorSchema::AUTHOR_SCHEMA);

        $this->assertSame([
            'source'         => 'string',
            'altText'        => 'string',
            'titleText'      => 'string',
            'licenseName'    => 'string',
            'licenseUrl'     => 'string',
            'authorName'     => 'string',
            'authorUrl'      => 'string',
            'copyright'      => 'string',
        ], FeaturedImageSchema::FEATURED_IMAGE_SCHEMA);
    }

    public function testAllSchemasAreTested()
    {
        $files = glob('vendor/hyde/framework/src/Markdown/Contracts/FrontMatter/*Schema.php');
        $subFiles = glob('vendor/hyde/framework/src/Markdown/Contracts/FrontMatter/SubSchemas/*Schema.php');
        $this->assertNotEmpty($files, 'No schemas found.');
        $this->assertNotEmpty($subFiles, 'No sub schemas found.');

        $schemas = array_map(function ($file) {
            return 'Hyde\\Markdown\\Contracts\\FrontMatter\\'.basename($file, '.php');
        }, $files);

        $subSchemas = array_map(function ($file) {
            return 'Hyde\\Markdown\\Contracts\\FrontMatter\\SubSchemas\\'.basename($file, '.php');
        }, $subFiles);

        $schemas = array_merge($schemas, $subSchemas);

        $schemas = array_filter($schemas, function ($schema) {
            return $schema !== 'Hyde\\Markdown\\Contracts\\FrontMatter\\FrontMatterSchema';
        });

        $schemas = array_values($schemas);

        $this->assertEquals(self::SCHEMAS, $schemas);
    }

    public function testAllSchemasExtendFrontMatterSchemaInterface()
    {
        foreach (self::SCHEMAS as $schema) {
            $this->assertTrue(is_subclass_of($schema, FrontMatterSchema::class));
        }
    }

    public function testAllSchemasHaveConstantMatchingTheirInterfaceName()
    {
        foreach (self::SCHEMAS as $schema) {
            $this->assertClassHasConstant($this->classToConstant($schema), $schema);
        }
    }

    public function testEachInterfaceOnlyHasOneSchema()
    {
        $files = glob('vendor/hyde/framework/src/Markdown/Contracts/FrontMatter/*Schema.php');
        $subFiles = glob('vendor/hyde/framework/src/Markdown/Contracts/FrontMatter/SubSchemas/*Schema.php');
        $files = array_merge($files, $subFiles);

        foreach ($files as $file) {
            $contents = file_get_contents($file);
            $this->assertLessThanOrEqual(1,
                substr_count($contents, 'public const'),
                "File $file has more than one constant defined."
            );
        }
    }

    private function assertClassHasConstant(string $constant, string $schema)
    {
        $this->assertTrue(
            defined("$schema::$constant"),
            "Class $schema does not have a constant named $constant."
        );
    }

    protected function classToConstant(string $schema): string
    {
        return Str::upper(Str::snake(Str::afterLast($schema, '\\')));
    }
}
