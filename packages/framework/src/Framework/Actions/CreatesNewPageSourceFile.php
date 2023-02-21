<?php

declare(strict_types=1);

namespace Hyde\Framework\Actions;

use function file_exists;
use Hyde\Framework\Concerns\InteractsWithDirectories;
use Hyde\Framework\Exceptions\FileConflictException;
use Hyde\Framework\Exceptions\UnsupportedPageTypeException;
use Hyde\Hyde;
use Hyde\Pages\BladePage;
use Hyde\Pages\DocumentationPage;
use Hyde\Pages\MarkdownPage;
use Illuminate\Support\Str;
use function in_array;
use function rtrim;
use function unslash;

/**
 * Scaffold a new Markdown, Blade, or documentation page.
 *
 * @see \Hyde\Framework\Testing\Feature\Actions\CreatesNewPageSourceFileTest
 */
class CreatesNewPageSourceFile
{
    use InteractsWithDirectories;

    protected string $title;
    protected string $filename;
    protected string $outputPath;
    protected string $subDir = '';
    protected bool $force;

    public function __construct(string $title, string $type = MarkdownPage::class, bool $force = false)
    {
        $this->validateType($type);

        $this->title = $this->parseTitle($title);
        $this->filename = $this->fileName($title);
        $this->force = $force;

        $this->outputPath = $this->makeOutputPath($type);

        $this->createPage($type);
    }

    public function getOutputPath(): string
    {
        return $this->outputPath;
    }

    protected function parseTitle(string $title): string
    {
        return Str::afterLast($title, '/');
    }

    protected function fileName(string $title): string
    {
        // If title contains a slash, it's a subdirectory
        if (str_contains($title, '/')) {
            // So we normalize the subdirectory name
            $this->subDir = $this->normalizeSubdirectory($title);
        }

        // And return a slug made from just the title without the subdirectory
        return Str::slug(basename($title));
    }

    protected function normalizeSubdirectory(string $title): string
    {
        return unslash('/'.rtrim(Str::beforeLast($title, '/').'/', '/\\'));
    }

    /** @param class-string<\Hyde\Pages\Concerns\HydePage> $pageClass */
    protected function makeOutputPath(string $pageClass): string
    {
        return Hyde::path($pageClass::sourcePath($this->formatIdentifier()));
    }

    protected function createPage(string $type): void
    {
        match ($type) {
            BladePage::class => $this->createBladeFile(),
            MarkdownPage::class => $this->createMarkdownFile(),
            DocumentationPage::class => $this->createDocumentationFile(),
        };
    }

    protected function createBladeFile(): void
    {
        $this->createFile(<<<BLADE
            @extends('hyde::layouts.app')
            @section('content')
            @php(\$title = "$this->title")
            
            <main class="mx-auto max-w-7xl py-16 px-8">
                <h1 class="text-center text-3xl font-bold">$this->title</h1>
            </main>
            
            @endsection

            BLADE
        );
    }

    protected function createMarkdownFile(): void
    {
        $this->failIfFileCannotBeSaved($this->outputPath);

        (new MarkdownPage($this->formatIdentifier(), ['title' => $this->title], "# $this->title"))->save();
    }

    protected function createDocumentationFile(): void
    {
        $this->failIfFileCannotBeSaved($this->outputPath);

        (new DocumentationPage($this->formatIdentifier(), [], "# $this->title\n"))->save();
    }

    protected function formatIdentifier(): string
    {
        return "$this->subDir/$this->filename";
    }

    protected function validateType(string $pageClass): void
    {
        if (! in_array($pageClass, [MarkdownPage::class, BladePage::class, DocumentationPage::class])) {
            throw new UnsupportedPageTypeException('The page type must be either "markdown", "blade", or "documentation"');
        }
    }

    protected function failIfFileCannotBeSaved(string $path): void
    {
        if ($this->force !== true && file_exists($path)) {
            throw new FileConflictException($path);
        }
    }

    protected function prepareOutputDirectory(): void
    {
        $this->needsParentDirectory($this->outputPath);
        $this->failIfFileCannotBeSaved($this->outputPath);
    }

    protected function createFile(string $contents): void
    {
        $this->prepareOutputDirectory();

        file_put_contents($this->outputPath, Hyde::normalizeNewlines($contents));
    }
}
