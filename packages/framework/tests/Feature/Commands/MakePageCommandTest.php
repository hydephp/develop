<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature\Commands;

use Exception;
use Hyde\Facades\Filesystem;
use Hyde\Framework\Exceptions\FileConflictException;
use Hyde\Hyde;
use Hyde\Testing\TestCase;

/**
 * @covers \Hyde\Console\Commands\MakePageCommand
 * @covers \Hyde\Framework\Actions\CreatesNewPageSourceFile
 */
class MakePageCommandTest extends TestCase
{
    protected string $markdownPath;
    protected string $bladePath;

    public function setUp(): void
    {
        parent::setUp();

        $this->markdownPath = Hyde::path('_pages/foo-test-page.md');
        $this->bladePath = Hyde::path('_pages/foo-test-page.blade.php');
    }

    protected function tearDown(): void
    {
        if (file_exists($this->markdownPath)) {
            unlink($this->markdownPath);
        }

        if (file_exists($this->bladePath)) {
            unlink($this->bladePath);
        }

        parent::tearDown();
    }

    public function testCommandCanRun()
    {
        $this->artisan('make:page "foo test page"')->assertExitCode(0);
    }

    public function testCommandOutput()
    {
        $this->artisan('make:page "foo test page"')
            ->expectsOutputToContain('Creating a new page!')
            ->expectsOutputToContain('Created file '.$this->markdownPath)
            ->assertExitCode(0);
    }

    public function testCommandAllowsUserToSpecifyPageType()
    {
        $this->artisan('make:page "foo test page" --type=markdown')->assertExitCode(0);
        $this->artisan('make:page "foo test page" --type=blade')->assertExitCode(0);
    }

    public function testTypeOptionIsCaseInsensitive()
    {
        $this->artisan('make:page "foo test page" --type=Markdown')->assertExitCode(0);
        $this->artisan('make:page "foo test page" --type=Blade')->assertExitCode(0);
    }

    public function testCommandFailsIfUserSpecifiesInvalidPageType()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('The page type [invalid] is not supported.');
        $this->expectExceptionCode(400);
        $this->artisan('make:page "foo test page" --type=invalid')->assertExitCode(400);
    }

    public function testCommandCreatesMarkdownFile()
    {
        $this->artisan('make:page "foo test page"')->assertExitCode(0);

        $this->assertFileExists($this->markdownPath);
    }

    public function testCommandCreatesBladeFile()
    {
        $this->artisan('make:page "foo test page" --type="blade"')->assertExitCode(0);

        $this->assertFileExists($this->bladePath);
    }

    public function testCommandCreatesDocumentationFile()
    {
        $this->artisan('make:page "foo test page" --type="documentation"')->assertExitCode(0);

        $this->assertFileExists(Hyde::path('_docs/foo-test-page.md'));
        Filesystem::unlink('_docs/foo-test-page.md');
    }

    public function testCommandFailsIfFileAlreadyExists()
    {
        file_put_contents($this->markdownPath, 'This should not be overwritten');

        $this->expectException(FileConflictException::class);
        $this->expectExceptionMessage('File [_pages/foo-test-page.md] already exists.');
        $this->expectExceptionCode(409);
        $this->artisan('make:page "foo test page"')->assertExitCode(409);

        $this->assertEquals('This should not be overwritten', file_get_contents($this->markdownPath));
    }

    public function testCommandOverwritesExistingFilesWhenForceOptionIsUsed()
    {
        file_put_contents($this->markdownPath, 'This should be overwritten');

        $this->artisan('make:page "foo test page" --force')->assertExitCode(0);

        $this->assertNotEquals('This should be overwritten', file_get_contents($this->markdownPath));
    }

    public function testCommandPromptsForTitleIfItWasNotSpecified()
    {
        $this->artisan('make:page')
            ->expectsQuestion('What is the title of the page?', 'Test Page')
            ->expectsOutput("Creating a new Markdown page with title: Test Page\n")
            ->assertExitCode(0);

        Filesystem::unlink('_pages/test-page.md');
    }

    public function testCommandFallsBackToDefaultTitleIfUserEntersNothing()
    {
        $this->artisan('make:page')
            ->expectsQuestion('What is the title of the page?', null)
            ->expectsOutput("Creating a new Markdown page with title: My New Page\n")
            ->assertExitCode(0);

        Filesystem::unlink('_pages/my-new-page.md');
    }

    public function testPageTypeShorthandCanBeUsedToCreateBladePages()
    {
        $this->artisan('make:page "foo test page" --blade')
            ->expectsOutput("Creating a new Blade page with title: foo test page\n")
            ->assertExitCode(0);

        $this->assertFileExists($this->bladePath);
    }

    public function testPageTypeShorthandCanBeUsedToCreateDocumentationPages()
    {
        $this->artisan('make:page "foo test page" --docs')
            ->expectsOutput("Creating a new Documentation page with title: foo test page\n")
            ->assertExitCode(0);

        $this->assertFileExists(Hyde::path('_docs/foo-test-page.md'));
        Filesystem::unlink('_docs/foo-test-page.md');
    }
}
