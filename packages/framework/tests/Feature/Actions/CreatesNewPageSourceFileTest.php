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
    public function testClassCanBeInstantiated()
    {
        $this->assertInstanceOf(
            CreatesNewPageSourceFile::class,
            new CreatesNewPageSourceFile('Test Page')
        );
    }

    public function testThatAnExceptionIsThrownForInvalidPageType()
    {
        $this->expectException(UnsupportedPageTypeException::class);
        $this->expectExceptionMessage('The page type must be either "markdown", "blade", or "documentation"');

        (new CreatesNewPageSourceFile('Test Page', 'invalid'))->save();
    }

    public function testThatAnExceptionIsThrownIfFileAlreadyExistsAndOverwriteIsFalse()
    {
        $this->file('_pages/foo.md', 'foo');

        $this->expectException(FileConflictException::class);
        $this->expectExceptionMessage('File [_pages/foo.md] already exists.');
        $this->expectExceptionCode(409);

        (new CreatesNewPageSourceFile('foo'))->save();
        $this->assertSame('foo', file_get_contents(Hyde::path('_pages/foo.md')));
        Filesystem::unlink('_pages/foo.md');
    }

    public function testThatCanSaveFileReturnsTrueIfFileAlreadyExistsAndOverwriteIsTrue()
    {
        $this->file('_pages/foo.md', 'foo');

        (new CreatesNewPageSourceFile('foo', force: true))->save();
        $this->assertSame("---\ntitle: foo\n---\n\n# foo\n", file_get_contents(Hyde::path('_pages/foo.md')));
        Filesystem::unlink('_pages/foo.md');
    }

    public function testExceptionIsThrownForConflictingBladePages()
    {
        $this->file('_pages/foo.blade.php', 'foo');

        $this->expectException(FileConflictException::class);
        $this->expectExceptionMessage('File [_pages/foo.blade.php] already exists.');
        $this->expectExceptionCode(409);

        (new CreatesNewPageSourceFile('foo', BladePage::class))->save();
        $this->assertSame('foo', file_get_contents(Hyde::path('_pages/foo.blade.php')));
        Filesystem::unlink('_pages/foo.blade.php');
    }

    public function testExceptionIsThrownForConflictingDocumentationPages()
    {
        $this->file('_docs/foo.md', 'foo');

        $this->expectException(FileConflictException::class);
        $this->expectExceptionMessage('File [_docs/foo.md] already exists.');
        $this->expectExceptionCode(409);

        (new CreatesNewPageSourceFile('foo', DocumentationPage::class))->save();
        $this->assertSame('foo', file_get_contents(Hyde::path('_docs/foo.md')));
        Filesystem::unlink('_docs/foo.md');
    }

    public function testThatAMarkdownFileCanBeCreatedAndContainsExpectedContent()
    {
        (new CreatesNewPageSourceFile('Test Page'))->save();

        $this->assertFileExists(Hyde::path('_pages/test-page.md'));

        $this->assertSame(
            "---\ntitle: 'Test Page'\n---\n\n# Test Page\n",
            file_get_contents(Hyde::path('_pages/test-page.md'))
        );
        Filesystem::unlink('_pages/test-page.md');
    }

    public function testThatABladeFileCanBeCreatedAndContainsExpectedContent()
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

    public function testThatADocumentationFileCanBeCreatedAndContainsExpectedContent()
    {
        (new CreatesNewPageSourceFile('Test Page', DocumentationPage::class))->save();

        $this->assertFileExists(Hyde::path('_docs/test-page.md'));

        $this->assertSame(
            "# Test Page\n",
            file_get_contents(Hyde::path('_docs/test-page.md'))
        );

        Filesystem::unlink('_docs/test-page.md');
    }

    public function testThatAMarkdownFileCanBeCreatedWithCustomContent()
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

    public function testThatABladeFileCanBeCreatedWithCustomContent()
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

    public function testThatTheFilePathCanBeReturned()
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

    public function testFileIsCreatedUsingSlugGeneratedFromTitle()
    {
        (new CreatesNewPageSourceFile('Foo Bar'))->save();
        $this->assertFileExists(Hyde::path('_pages/foo-bar.md'));
        Filesystem::unlink('_pages/foo-bar.md');
    }

    public function testActionCanGenerateNestedPages()
    {
        (new CreatesNewPageSourceFile('foo/bar'))->save();
        $this->assertFileExists(Hyde::path('_pages/foo/bar.md'));
        Filesystem::deleteDirectory('_pages/foo');
    }

    public function testCanCreateDeeplyNestedPages()
    {
        (new CreatesNewPageSourceFile('/foo/bar/Foo Bar'))->save();
        $this->assertFileExists(Hyde::path('_pages/foo/bar/foo-bar.md'));
        Filesystem::deleteDirectory('_pages/foo');
    }
}
