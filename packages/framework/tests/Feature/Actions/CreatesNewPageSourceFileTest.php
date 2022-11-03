<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature\Actions;

use Hyde\Framework\Actions\CreatesNewPageSourceFile;
use Hyde\Framework\Exceptions\FileConflictException;
use Hyde\Framework\Exceptions\UnsupportedPageTypeException;
use Hyde\Hyde;
use Hyde\Pages\BladePage;
use Hyde\Pages\DocumentationPage;
use Hyde\Testing\TestCase;

/**
 * @covers \Hyde\Framework\Actions\CreatesNewPageSourceFile
 */
class CreatesNewPageSourceFileTest extends TestCase
{
    protected function tearDown(): void
    {
        if (file_exists(Hyde::path('_pages/test-page.md'))) {
            unlink(Hyde::path('_pages/test-page.md'));
        }

        if (file_exists(Hyde::path('_pages/test-page.blade.php'))) {
            unlink(Hyde::path('_pages/test-page.blade.php'));
        }

        parent::tearDown();
    }

    public function test_class_can_be_instantiated()
    {
        $this->assertInstanceOf(
            CreatesNewPageSourceFile::class,
            new CreatesNewPageSourceFile('Test Page')
        );
    }

    public function test_that_a_slug_is_generated_from_the_title()
    {
        $this->assertEquals(
            'test-page',
            (new CreatesNewPageSourceFile('Test Page'))->slug
        );
    }

    public function test_that_an_exception_is_thrown_for_invalid_page_type()
    {
        $this->expectException(UnsupportedPageTypeException::class);
        $this->expectExceptionMessage('The page type must be either "markdown", "blade", or "documentation"');

        (new CreatesNewPageSourceFile('Test Page', 'invalid'));
    }

    public function test_that_an_exception_is_thrown_if_file_already_exists_and_overwrite_is_false()
    {
        $path = Hyde::path('_pages/foo.md');
        file_put_contents($path, 'foo');

        $this->expectException(FileConflictException::class);
        $this->expectExceptionMessage("File already exists: $path");
        $this->expectExceptionCode(409);

        new CreatesNewPageSourceFile('foo');

        unlink($path);
    }

    public function test_that_can_save_file_returns_true_if_file_already_exists_and_overwrite_is_true()
    {
        $path = Hyde::path('_pages/foo.md');
        file_put_contents($path, 'foo');

        new CreatesNewPageSourceFile('foo', force: true);

        $this->assertTrue(true);
        unlink($path);
    }

    public function test_that_a_markdown_file_can_be_created_and_contains_expected_content()
    {
        (new CreatesNewPageSourceFile('Test Page'));

        $this->assertFileExists(
            Hyde::path('_pages/test-page.md')
        );

        $this->assertEquals(
            "---\ntitle: Test Page\n---\n\n# Test Page\n",
            file_get_contents(Hyde::path('_pages/test-page.md'))
        );
    }

    public function test_that_a_blade_file_can_be_created_and_contains_expected_content()
    {
        (new CreatesNewPageSourceFile('Test Page', BladePage::class));

        $this->assertFileExists(
            Hyde::path('_pages/test-page.blade.php')
        );

        $fileContent = file_get_contents(Hyde::path('_pages/test-page.blade.php'));
        $this->assertStringContainsString(
            '@extends(\'hyde::layouts.app\')',
            $fileContent
        );
        $this->assertStringContainsString(
            '@php($title = "Test Page")',
            $fileContent
        );
        $this->assertStringContainsString(
            '<h1 class="text-center text-3xl font-bold">Test Page</h1>',
            $fileContent
        );
    }

    public function test_that_a_documentation_file_can_be_created_and_contains_expected_content()
    {
        (new CreatesNewPageSourceFile('Test Page', DocumentationPage::class));

        $this->assertFileExists(
            Hyde::path('_docs/test-page.md')
        );

        $this->assertEquals(
            "# Test Page\n",
            file_get_contents(Hyde::path('_docs/test-page.md'))
        );

        Hyde::unlink('_docs/test-page.md');
    }

    public function test_that_the_file_path_can_be_returned()
    {
        $this->assertEquals(
            Hyde::path('_pages/test-page.md'),
            (new CreatesNewPageSourceFile('Test Page'))->outputPath
        );

        $this->assertEquals(
            Hyde::path('_pages/test-page.blade.php'),
            (new CreatesNewPageSourceFile('Test Page', BladePage::class))->outputPath
        );
    }

    public function test_parse_slug_returns_slug_generated_from_title()
    {
        $action = new CreatesNewPageSourceFile('Foo Bar');
        $this->assertEquals('foo-bar', $action->parseSlug('Foo Bar'));
        Hyde::unlink('_pages/foo-bar.md');
    }

    public function test_parse_slug_does_not_include_path_information()
    {
        $action = new CreatesNewPageSourceFile('Foo Bar');
        $this->assertEquals('foo-bar', $action->parseSlug('/foo/bar/Foo Bar'));
        Hyde::unlink('_pages/foo-bar.md');
    }

    public function test_parse_slug_sets_sub_dir_property_for_nested_pages()
    {
        $action = new CreatesNewPageSourceFile('foo');
        $this->assertEquals('bar', $action->parseSlug('/foo/bar'));
        $this->assertEquals('/foo/', $action->subDir);
        Hyde::unlink('_pages/foo.md');
    }

    public function test_action_can_generate_nested_pages()
    {
        new CreatesNewPageSourceFile('foo/bar');
        $this->assertFileExists(Hyde::path('_pages/foo/bar.md'));
        Hyde::unlink('_pages/foo/bar.md');
        rmdir(Hyde::path('_pages/foo'));
    }
}
