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
    protected string $slug;
    protected string $outputPath;
    protected string $subDir = '';
    protected bool $force = false;

    public function __construct(string $title, string $type = MarkdownPage::class, bool $force = false)
    {
        $this->title = $this->parseTitle($title);
        $this->slug = $this->parseSlug($title);
        $this->force = $force;

        $this->createPage($type);
    }

    protected function parseTitle(string $title): string
    {
        return Str::afterLast($title, '/');
    }

    protected function parseSlug(string $title): string
    {
        // If title contains a slash, it's a subdirectory
        if (str_contains($title, '/')) {
            // So we normalize the subdirectory name
            $this->subDir = $this->parseSubDir($title);
        }

        // And return a slug made from just the title without the subdirectory
        return Str::slug(basename($title));
    }

    protected function parseSubDir(string $title): string
    {
        $subDir = Str::beforeLast($title, '/').'/';

        if ($subDir !== '') {
            $subDir = unslash('/'.rtrim($subDir, '/\\'));
        }

        return $subDir;
    }

    protected function createPage(string $type): int|false
    {
        return match ($type) {
            BladePage::class => $this->createBladeFile(),
            MarkdownPage::class => $this->createMarkdownFile(),
            DocumentationPage::class => $this->createDocumentationFile(),
            default => throw new UnsupportedPageTypeException('The page type must be either "markdown", "blade", or "documentation"')
        };
    }

    protected function createBladeFile(): int|false
    {
        $this->outputPath = Hyde::path(BladePage::sourcePath($this->formatIdentifier()));
        $this->needsParentDirectory($this->outputPath);

        $this->failIfFileCannotBeSaved($this->outputPath);

        return file_put_contents(
            $this->outputPath,
            <<<BLADE
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

    protected function createMarkdownFile(): int|false
    {
        $this->outputPath = Hyde::path(MarkdownPage::sourcePath($this->formatIdentifier()));
        $this->needsParentDirectory($this->outputPath);

        $this->failIfFileCannotBeSaved($this->outputPath);

        return file_put_contents(
            $this->outputPath,
            "---\ntitle: $this->title\n---\n\n# $this->title\n"
        );
    }

    protected function createDocumentationFile(): int|false
    {
        $this->outputPath = Hyde::path(DocumentationPage::sourcePath($this->formatIdentifier()));
        $this->needsParentDirectory($this->outputPath);

        $this->failIfFileCannotBeSaved($this->outputPath);

        return file_put_contents(
            $this->outputPath,
            "# $this->title\n"
        );
    }

    protected function formatIdentifier(): string
    {
        return "$this->subDir/$this->slug";
    }

    protected function failIfFileCannotBeSaved(string $path): void
    {
        if (file_exists($path) && ! $this->force) {
            throw new FileConflictException($path);
        }
    }

    public function getOutputPath(): string
    {
        return $this->outputPath;
    }
}
