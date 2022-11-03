<?php

declare(strict_types=1);

namespace Hyde\Framework\Actions;

use Hyde\Framework\Concerns\InteractsWithDirectories;
use Hyde\Framework\Exceptions\FileConflictException;
use Illuminate\Support\Str;

/**
 * Scaffold a new Markdown, Blade, or documentation page.
 *
 * @see \Hyde\Framework\Testing\Feature\Actions\CreatesNewPageSourceFileTest
 */
class CreatesNewPublicationTypeSchema
{
    use InteractsWithDirectories;

    public function __construct(
        protected string $name,
        protected array $fields,
        protected string $canonicalField,
        protected string $sortField,
        protected string $sortDirection
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
        $name = Str::camel($this->name);
        @mkdir($name);

        $data                   = [];
        $data['name']           = $this->name;
        $data['canonicalField'] = $this->canonicalField;
        $data['sortField']      = $this->sortField;
        $data['sortDirection']  = $this->sortDirection;
        $data['detailTemplate'] = "{$name}.detail.blade.php";
        $data['listTemplate']   = "{$name}.list.blade.php";
        $data['fields']         = $this->fields;
        $json                   = json_encode($data, JSON_PRETTY_PRINT);

        return file_put_contents("$name/schema.json", $json);
    }
}
