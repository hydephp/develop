<?php

declare(strict_types=1);

namespace Hyde\Framework\Actions;

use Hyde\Framework\Actions\Interfaces\CreateActionInterface;
use Hyde\Framework\Concerns\InteractsWithDirectories;
use Illuminate\Support\Str;
use Rgasch\Collection;

/**
 * Scaffold a new Markdown, Blade, or documentation page.
 *
 * @see \Hyde\Framework\Testing\Feature\Actions\CreatesNewPageSourceFileTest
 */
class CreatesNewPublicationTypeSchema implements CreateActionInterface
{
    use InteractsWithDirectories;

    public function __construct(
        protected string $name,
        protected Collection $fields,
        protected string $canonicalField,
        protected string $sortField,
        protected string $sortDirection
    ) {
    }


    public function create(): string|bool
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

        return (bool)file_put_contents("$name/schema.json", $json);
    }
}
