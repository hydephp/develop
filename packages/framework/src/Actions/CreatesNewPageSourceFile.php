<?php

namespace Hyde\Framework\Actions;

use Hyde\Framework\Exceptions\FileConflictException;
use Hyde\Framework\Exceptions\UnsupportedPageTypeException;
use Hyde\Framework\Hyde;
use Hyde\Framework\Models\BladePage;
use Hyde\Framework\Models\DocumentationPage;
use Hyde\Framework\Models\MarkdownPage;
use Illuminate\Support\Str;

/**
 * Scaffold a new Markdown, Blade, or documentation page.
 *
 * @see \Hyde\Framework\Testing\Feature\Actions\CreatesNewPageSourceFileTest
 */
class CreatesNewPageSourceFile
{
    public string $title;
    public string $slug;
    public string $outputPath;

    public function __construct(string $title, string $type = MarkdownPage::class, public bool $force = false)
    {
        $this->title = $title;
        $this->slug = Str::slug($title);

        $this->createPage($type);
    }

    public function canSaveFile(string $path): void
    {
        if (file_exists($path) && ! $this->force) {
            throw new FileConflictException($path);
        }
    }

    public function createPage(string $type): int|false
    {
        if ($type === MarkdownPage::class) {
            return $this->createMarkdownFile();
        }
        if ($type === BladePage::class) {
            return $this->createBladeFile();
        }

        if ($type === DocumentationPage::class) {
            return $this->createDocumentationFile();
        }

        throw new UnsupportedPageTypeException('The page type must be either "markdown", "blade", or "documentation"');
    }

    public function createMarkdownFile(): int|false
    {
        $this->outputPath = Hyde::path("_pages/$this->slug.md");

        $this->canSaveFile($this->outputPath);

        return file_put_contents(
            $this->outputPath,
            "---\ntitle: $this->title\n---\n\n# $this->title\n"
        );
    }

    public function createBladeFile(): int|false
    {
        $this->outputPath = Hyde::path("_pages/$this->slug.blade.php");

        $this->canSaveFile($this->outputPath);

        return file_put_contents(
            $this->outputPath,
            <<<EOF
@extends('hyde::layouts.app')
@section('content')
@php(\$title = "$this->title")

<main class="mx-auto max-w-7xl py-16 px-8">
	<h1 class="text-center text-3xl font-bold">$this->title</h1>
</main>

@endsection

EOF
        );
    }

    public function createDocumentationFile(): int|false
    {
        $this->outputPath = Hyde::path("_docs/$this->slug.md");

        $this->canSaveFile($this->outputPath);

        return file_put_contents(
            $this->outputPath,
            "# $this->title\n"
        );
    }
}
