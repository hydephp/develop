<?php

declare(strict_types=1);

namespace Hyde\Framework\Actions;

use Hyde\Hyde;
use Hyde\Pages\BladePage;
use Illuminate\Support\Str;
use Hyde\Pages\MarkdownPage;
use Hyde\Pages\DocumentationPage;
use Hyde\Framework\Exceptions\FileConflictException;
use Hyde\Framework\Concerns\InteractsWithDirectories;
use Hyde\Framework\Exceptions\UnsupportedPageTypeException;

use function file_put_contents;
use function file_exists;
use function basename;
use function in_array;
use function str_contains;
use function unslash;
use function rtrim;

/**
 * Scaffold a new Markdown, Blade, or documentation page.
 */
class CreatesNewPageSourceFile
{
    use InteractsWithDirectories;

    /** @var class-string<\Hyde\Pages\Concerns\HydePage> */
    protected string $pageClass;

    protected string $title;
    protected string $filename;
    protected string $outputPath;
    protected string $subDir = '';
    protected bool $force;

    protected ?string $customContent;

    public function __construct(string $title, string $pageClass = MarkdownPage::class, bool $force = false, ?string $customContent = null)
    {
        $this->validateType($pageClass);
        $this->pageClass = $pageClass;

        $this->title = $this->parseTitle($title);
        $this->filename = $this->fileName($title);
        $this->force = $force;
        $this->customContent = $customContent;

        $this->outputPath = $this->makeOutputPath($pageClass);
    }

    public function save(): string
    {
        $this->failIfFileCannotBeSaved($this->outputPath);

        match ($this->pageClass) {
            BladePage::class => $this->createBladeFile(),
            MarkdownPage::class => $this->createMarkdownFile(),
            DocumentationPage::class => $this->createDocumentationFile(),
        };

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

    protected function createBladeFile(): void
    {
        $this->needsParentDirectory($this->outputPath);

        file_put_contents($this->outputPath, Hyde::normalizeNewlines(<<<BLADE
            @extends('hyde::layouts.app')
            @section('content')
            @php(\$title = "$this->title")

            <main class="mx-auto max-w-7xl py-16 px-8">
                {$this->getPageContent()}
            </main>

            @endsection

            BLADE
        ));
    }

    protected function createMarkdownFile(): void
    {
        (new MarkdownPage($this->formatIdentifier(), ['title' => $this->title], $this->getPageContent()))->save();
    }

    protected function createDocumentationFile(): void
    {
        (new DocumentationPage($this->formatIdentifier(), [], "# $this->title"))->save();
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

    protected function getPageContent(): string
    {
        return $this->customContent ?? ($this->pageClass === BladePage::class
            ? "<h1 class=\"text-center text-3xl font-bold\">$this->title</h1>"
            : "# $this->title");
    }
}
