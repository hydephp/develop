<?php

namespace Hyde\Framework\Testing\Unit;

use Hyde\Framework\Hyde;
use Hyde\Framework\Models\Pages\BladePage;
use Hyde\Framework\Models\DocumentationPage;
use Hyde\Framework\Models\Pages\MarkdownPage;
use Hyde\Framework\Models\Pages\MarkdownPost;
use Hyde\Testing\TestCase;

/**
 * Class FluentPathHelpersTest.
 *
 * @deprecated Move to Feature namespace
 * @covers \Hyde\Framework\Concerns\Internal\FluentPathHelpers
 */
class FluentPathHelpersTest extends TestCase
{
    public function test_get_model_source_path_method_returns_path_for_model_classes()
    {
        $this->assertEquals(
            Hyde::path('_posts'),
            Hyde::getModelSourcePath(MarkdownPost::class)
        );

        $this->assertEquals(
            Hyde::path('_pages'),
            Hyde::getModelSourcePath(MarkdownPage::class)
        );

        $this->assertEquals(
            Hyde::path('_docs'),
            Hyde::getModelSourcePath(DocumentationPage::class)
        );

        $this->assertEquals(
            Hyde::path('_pages'),
            Hyde::getModelSourcePath(BladePage::class)
        );
    }

    public function test_get_model_source_path_method_returns_path_to_file_for_model_classes()
    {
        $this->assertEquals(
            Hyde::path('_posts'.DIRECTORY_SEPARATOR.'foo.md'),
            Hyde::getModelSourcePath(MarkdownPost::class, 'foo.md')
        );

        $this->assertEquals(
            Hyde::path('_pages'.DIRECTORY_SEPARATOR.'foo.md'),
            Hyde::getModelSourcePath(MarkdownPage::class, 'foo.md')
        );

        $this->assertEquals(
            Hyde::path('_docs'.DIRECTORY_SEPARATOR.'foo.md'),
            Hyde::getModelSourcePath(DocumentationPage::class, 'foo.md')
        );

        $this->assertEquals(
            Hyde::path('_pages'.DIRECTORY_SEPARATOR.'foo.md'),
            Hyde::getModelSourcePath(BladePage::class, 'foo.md')
        );
    }

    public function test_helper_for_blade_pages()
    {
        $this->assertEquals(
            Hyde::path('_pages'),
            Hyde::getBladePagePath()
        );
    }

    public function test_helper_for_markdown_pages()
    {
        $this->assertEquals(
            Hyde::path('_pages'),
            Hyde::getMarkdownPagePath()
        );
    }

    public function test_helper_for_markdown_posts()
    {
        $this->assertEquals(
            Hyde::path('_posts'),
            Hyde::getMarkdownPostPath()
        );
    }

    public function test_helper_for_documentation_pages()
    {
        $this->assertEquals(
            Hyde::path('_docs'),
            Hyde::getDocumentationPagePath()
        );
    }

    public function test_helper_for_site_output_path()
    {
        $this->assertEquals(
            Hyde::path('_site'),
            Hyde::getSiteOutputPath()
        );
    }

    public function test_helper_for_site_output_path_returns_path_to_file_within_the_directory()
    {
        $this->assertEquals(
            Hyde::path('_site'.DIRECTORY_SEPARATOR.'foo.html'),
            Hyde::getSiteOutputPath('foo.html')
        );
    }

    public function test_get_site_output_path_returns_absolute_path()
    {
        $this->assertEquals(
            Hyde::path('_site'),
            Hyde::getSiteOutputPath()
        );
    }

    public function test_site_output_path_helper_ignores_trailing_slashes()
    {
        $this->assertEquals(
            Hyde::path('_site'.DIRECTORY_SEPARATOR.'foo.html'),
            Hyde::getSiteOutputPath('/foo.html/')
        );
    }

    public function test_path_to_relative_helper_decodes_hyde_path_into_relative()
    {
        $s = DIRECTORY_SEPARATOR;
        $this->assertEquals('foo', Hyde::pathToRelative(Hyde::path('foo')));
        $this->assertEquals('foo', Hyde::pathToRelative(Hyde::path('/foo/')));
        $this->assertEquals('foo.md', Hyde::pathToRelative(Hyde::path('foo.md')));
        $this->assertEquals("foo{$s}bar", Hyde::pathToRelative(Hyde::path("foo{$s}bar")));
        $this->assertEquals("foo{$s}bar.md", Hyde::pathToRelative(Hyde::path("foo{$s}bar.md")));
    }

    public function test_path_to_relative_helper_does_not_modify_already_relative_paths()
    {
        $this->assertEquals('foo', Hyde::pathToRelative('foo'));
        $this->assertEquals('foo/', Hyde::pathToRelative('foo/'));
        $this->assertEquals('../foo', Hyde::pathToRelative('../foo'));
        $this->assertEquals('../foo/', Hyde::pathToRelative('../foo/'));
        $this->assertEquals('foo.md', Hyde::pathToRelative('foo.md'));
        $this->assertEquals('foo/bar', Hyde::pathToRelative('foo/bar'));
        $this->assertEquals('foo/bar.md', Hyde::pathToRelative('foo/bar.md'));
    }

    public function test_path_to_relative_helper_does_not_modify_non_project_paths()
    {
        $testStrings = [
            'C:\Documents\Newsletters\Summer2018.pdf',
            '\Program Files\Custom Utilities\StringFinder.exe',
            '2018\January.xlsx',
            '..\Publications\TravelBrochure.pdf',
            'C:\Projects\library\library.sln',
            'C:Projects\library\library.sln',
            '/home/seth/Pictures/penguin.jpg',
            '~/Pictures/penguin.jpg',
        ];

        foreach ($testStrings as $testString) {
            $this->assertEquals(
                $this->systemPath(($testString)),
                Hyde::pathToRelative(
                    $this->systemPath($testString)
                )
            );
        }
    }

    protected function systemPath(string $path): string
    {
        return str_replace('/', DIRECTORY_SEPARATOR, $path);
    }
}
