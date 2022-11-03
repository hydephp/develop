<?php

declare(strict_types=1);

namespace Hyde\Framework\Actions;

use Hyde\Framework\Concerns\InteractsWithDirectories;
use Hyde\Framework\Exceptions\FileConflictException;
use Hyde\Framework\Exceptions\UnsupportedPageTypeException;
use Hyde\Hyde;
use Hyde\Pages\BladePage;
use Hyde\Pages\DocumentationPage;
use Hyde\Pages\MarkdownPage;
use Illuminate\Support\Str;

/**
 * Scaffold a new Markdown, Blade, or documentation page.
 *
 * @see \Hyde\Framework\Testing\Feature\Actions\CreatesNewPageSourceFileTest
 */
class CreatesNewPageSourceFile
{
    use InteractsWithDirectories;

    public string $title;
    public string $slug;
    public string $outputPath;
    public string $subDir = '';
    public bool $force = false;

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

    public function parseSlug(string $title): string
    {
        if (str_contains($title, '/')) {
            $this->subDir = Str::beforeLast($title, '/').'/';
        }

        return Str::slug(basename($title));
    }

    protected function canSaveFile(string $path): void
    {
        if (file_exists($path) && ! $this->force) {
            throw new FileConflictException($path);
        }
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
        $this->needsDirectory(BladePage::sourceDirectory().$this->normalizeSubDir());
        $this->outputPath = Hyde::path(BladePage::sourcePath($this->formatIdentifier()));

        $this->canSaveFile($this->outputPath);

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
        $this->needsDirectory(MarkdownPage::sourceDirectory().$this->normalizeSubDir());
        $this->outputPath = Hyde::path(MarkdownPage::sourcePath($this->formatIdentifier()));

        $this->canSaveFile($this->outputPath);

        return file_put_contents(
            $this->outputPath,
            "---\ntitle: $this->title\n---\n\n# $this->title\n"
        );
    }

    protected function createDocumentationFile(): int|false
    {
        $this->needsDirectory(DocumentationPage::sourceDirectory().$this->normalizeSubDir());
        $this->outputPath = Hyde::path(DocumentationPage::sourcePath($this->formatIdentifier()));

        $this->canSaveFile($this->outputPath);

        return file_put_contents(
            $this->outputPath,
            "# $this->title\n"
        );
    }

    protected function formatIdentifier(): string
    {
        return $this->subDir.$this->slug;
    }

    protected function normalizeSubDir(): string
    {
        $subDir = $this->subDir;
        if ($subDir !== '') {
            $subDir = '/'.rtrim($subDir, '/\\');
        }

        return $subDir;
    }
}
