<?php

declare(strict_types=1);

namespace Hyde\Framework\Actions;

use Hyde\Framework\Concerns\InteractsWithDirectories;
use Hyde\Framework\Exceptions\FileConflictException;

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
        protected string $sortField
    ) {
        $this->createPage();
    }

    protected function canSaveFile(string $path): void
    {
        if (file_exists($path) && !$this->force) {
            throw new FileConflictException($path);
        }
    }

    protected function createPage(): int|false
    {
        @mkdir($this->name);

        $data                   = [];
        $data['name']           = $this->name;
        $data['sortField']      = $this->sortField;
        $data['detailTemplate'] = "{$this->name}.detail.blade.php";
        $data['listTemplate']   = "{$this->name}.list.blade.php";
        $data['fields']         = $this->fields;
        $json                   = json_encode($data, JSON_PRETTY_PRINT);

        return file_put_contents("$this->name/schema.json", $json);
    }
}
