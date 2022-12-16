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
use Illuminate\Support\Facades\File;

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

        $this->assertEqualsIgnoringLineEndingType(
            '@extends(\'hyde::layouts.app\')
@section(\'content\')
@php($title = "Test Page")

<main class="mx-auto max-w-7xl py-16 px-8">
    <h1 class="text-center text-3xl font-bold">Test Page</h1>
</main>

@endsection
', file_get_contents(Hyde::path('_pages/test-page.blade.php'))
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
            (new CreatesNewPageSourceFile('Test Page'))->getOutputPath()
        );

        $this->assertEquals(
            Hyde::path('_pages/test-page.blade.php'),
            (new CreatesNewPageSourceFile('Test Page', BladePage::class))->getOutputPath()
        );
    }

    public function test_file_is_created_using_slug_generated_from_title()
    {
        new CreatesNewPageSourceFile('Foo Bar');
        $this->assertFileExists(Hyde::path('_pages/foo-bar.md'));
        Hyde::unlink('_pages/foo-bar.md');
    }

    public function test_action_can_generate_nested_pages()
    {
        new CreatesNewPageSourceFile('foo/bar');
        $this->assertFileExists(Hyde::path('_pages/foo/bar.md'));
        File::deleteDirectory(Hyde::path('_pages/foo'));
    }

    public function test_can_create_deeply_nested_pages()
    {
        new CreatesNewPageSourceFile('/foo/bar/Foo Bar');
        $this->assertFileExists(Hyde::path('_pages/foo/bar/foo-bar.md'));
        File::deleteDirectory(Hyde::path('_pages/foo'));
    }
}
