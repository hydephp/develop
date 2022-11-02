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
class CreatesNewPublicationType
{
    use InteractsWithDirectories;

    public function __construct(
        protected string $name,
        protected array $fields,
        protected string $sortField)
    {
        $this->createPage();
    }

    protected function canSaveFile(string $path): void
    {
        if (file_exists($path) && ! $this->force) {
            throw new FileConflictException($path);
        }
    }

    protected function createPage(): int|false
    {
        $subDir = $this->name;
        if ($subDir !== '') {
            $subDir = '/'.rtrim($subDir, '/\\');
        }

        @mkdir($this->name);

        $data = [];
        $data['name'] = $this->name;
        $data['sortField'] = $this->sortField;
        $data['fields'] = $this->fields;
        $json = json_encode($data, JSON_PRETTY_PRINT);

        return file_put_contents("$this->name/schema.json", $json);
    }
}
