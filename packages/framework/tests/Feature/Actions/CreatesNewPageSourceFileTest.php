<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature\Actions;

use Hyde\Facades\Filesystem;
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

        (new CreatesNewPageSourceFile('Test Page', 'invalid'))->save();
    }

    public function test_that_an_exception_is_thrown_if_file_already_exists_and_overwrite_is_false()
    {
        $this->file('_pages/foo.md', 'foo');

        $this->expectException(FileConflictException::class);
        $this->expectExceptionMessage('File [_pages/foo.md] already exists.');
        $this->expectExceptionCode(409);

        (new CreatesNewPageSourceFile('foo'))->save();
        $this->assertSame('foo', file_get_contents(Hyde::path('_pages/foo.md')));
        Filesystem::unlink('_pages/foo.md');
    }

    public function test_that_can_save_file_returns_true_if_file_already_exists_and_overwrite_is_true()
    {
        $this->file('_pages/foo.md', 'foo');

        (new CreatesNewPageSourceFile('foo', force: true))->save();
        $this->assertSame("---\ntitle: foo\n---\n\n# foo\n", file_get_contents(Hyde::path('_pages/foo.md')));
        Filesystem::unlink('_pages/foo.md');
    }

    public function test_exception_is_thrown_for_conflicting_blade_pages()
    {
        $this->file('_pages/foo.blade.php', 'foo');

        $this->expectException(FileConflictException::class);
        $this->expectExceptionMessage('File [_pages/foo.blade.php] already exists.');
        $this->expectExceptionCode(409);

        (new CreatesNewPageSourceFile('foo', BladePage::class))->save();
        $this->assertSame('foo', file_get_contents(Hyde::path('_pages/foo.blade.php')));
        Filesystem::unlink('_pages/foo.blade.php');
    }

    public function test_exception_is_thrown_for_conflicting_documentation_pages()
    {
        $this->file('_docs/foo.md', 'foo');

        $this->expectException(FileConflictException::class);
        $this->expectExceptionMessage('File [_docs/foo.md] already exists.');
        $this->expectExceptionCode(409);

        (new CreatesNewPageSourceFile('foo', DocumentationPage::class))->save();
        $this->assertSame('foo', file_get_contents(Hyde::path('_docs/foo.md')));
        Filesystem::unlink('_docs/foo.md');
    }

    public function test_that_a_markdown_file_can_be_created_and_contains_expected_content()
    {
        (new CreatesNewPageSourceFile('Test Page'))->save();

        $this->assertFileExists(Hyde::path('_pages/test-page.md'));

        $this->assertSame(
            "---\ntitle: 'Test Page'\n---\n\n# Test Page\n",
            file_get_contents(Hyde::path('_pages/test-page.md'))
        );
        Filesystem::unlink('_pages/test-page.md');
    }

    public function test_that_a_blade_file_can_be_created_and_contains_expected_content()
    {
        (new CreatesNewPageSourceFile('Test Page', BladePage::class))->save();

        $this->assertFileExists(Hyde::path('_pages/test-page.blade.php'));

        $this->assertEquals(
            <<<'BLADE'
            @extends('hyde::layouts.app')
            @section('content')
            @php($title = "Test Page")

            <main class="mx-auto max-w-7xl py-16 px-8">
                <h1 class="text-center text-3xl font-bold">Test Page</h1>
            </main>

            @endsection

            BLADE, file_get_contents(Hyde::path('_pages/test-page.blade.php'))
        );

        Filesystem::unlink('_pages/test-page.blade.php');
    }

    public function test_that_a_documentation_file_can_be_created_and_contains_expected_content()
    {
        (new CreatesNewPageSourceFile('Test Page', DocumentationPage::class))->save();

        $this->assertFileExists(Hyde::path('_docs/test-page.md'));

        $this->assertSame(
            "# Test Page\n",
            file_get_contents(Hyde::path('_docs/test-page.md'))
        );

        Filesystem::unlink('_docs/test-page.md');
    }

    public function test_that_a_markdown_file_can_be_created_with_custom_content()
    {
        (new CreatesNewPageSourceFile('Test Page', customContent: 'Hello World!'))->save();

        $this->assertFileExists(Hyde::path('_pages/test-page.md'));

        $this->assertSame(
            <<<'MARKDOWN'
            ---
            title: 'Test Page'
            ---

            # Test Page

            Hello World!

            MARKDOWN
            ,
            file_get_contents(Hyde::path('_pages/test-page.md'))
        );
        Filesystem::unlink('_pages/test-page.md');
    }

    public function test_that_a_blade_file_can_be_created_with_custom_content()
    {
        (new CreatesNewPageSourceFile('Test Page', BladePage::class, customContent: 'Hello World!'))->save();

        $this->assertFileExists(Hyde::path('_pages/test-page.blade.php'));

        $this->assertEquals(
            <<<'BLADE'
            @extends('hyde::layouts.app')
            @section('content')
            @php($title = "Test Page")

            <main class="mx-auto max-w-7xl py-16 px-8">
                <h1 class="text-center text-3xl font-bold">Test Page</h1>

                <div>
                    Hello World!
                </div>
            </main>

            @endsection

            BLADE, file_get_contents(Hyde::path('_pages/test-page.blade.php'))
        );

        Filesystem::unlink('_pages/test-page.blade.php');
    }

    public function test_that_the_file_path_can_be_returned()
    {
        $this->assertSame(
            Hyde::path('_pages/test-page.md'),
            (new CreatesNewPageSourceFile('Test Page'))->save()
        );

        $this->assertSame(
            Hyde::path('_pages/test-page.blade.php'),
            (new CreatesNewPageSourceFile('Test Page', BladePage::class))->save()
        );

        Filesystem::unlink('_pages/test-page.md');
        Filesystem::unlink('_pages/test-page.blade.php');
    }

    public function test_file_is_created_using_slug_generated_from_title()
    {
        (new CreatesNewPageSourceFile('Foo Bar'))->save();
        $this->assertFileExists(Hyde::path('_pages/foo-bar.md'));
        Filesystem::unlink('_pages/foo-bar.md');
    }

    public function test_action_can_generate_nested_pages()
    {
        (new CreatesNewPageSourceFile('foo/bar'))->save();
        $this->assertFileExists(Hyde::path('_pages/foo/bar.md'));
        Filesystem::deleteDirectory('_pages/foo');
    }

    public function test_can_create_deeply_nested_pages()
    {
        (new CreatesNewPageSourceFile('/foo/bar/Foo Bar'))->save();
        $this->assertFileExists(Hyde::path('_pages/foo/bar/foo-bar.md'));
        Filesystem::deleteDirectory('_pages/foo');
    }
}
