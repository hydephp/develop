<?php

declare(strict_types=1);

namespace Hyde\Framework\Actions;

use Hyde\Framework\Actions\Interfaces\CreateActionInterface;
use Hyde\Framework\Concerns\InteractsWithDirectories;
use Hyde\HydeHelper;
use Rgasch\Collection\Collection;

/**
 * Scaffold a new Markdown, Blade, or documentation page.
 *
 * @see \Hyde\Framework\Testing\Feature\Actions\CreatesNewPageSourceFileTest
 */
class CreatesNewPublicationTypeSchema implements CreateActionInterface
{
    use InteractsWithDirectories;

    protected string $result;

    public function __construct(
        protected string $name,
        protected Collection $fields,
        protected string $canonicalField,
        protected string $sortField,
        protected string $sortDirection,
        protected int $pagesize
    ) {
    }


    public function create(): bool
    {
        $dirName = HydeHelper::formatNameForStorage($this->name);
        @mkdir($dirName);

        $data                   = [];
        $data['name']           = $this->name;
        $data['canonicalField'] = $this->canonicalField;
        $data['sortField']      = $this->sortField;
        $data['sortDirection']  = $this->sortDirection;
        $data['pagesize']       = $this->pagesize;
        $data['detailTemplate'] = "{$dirName}.detail.blade.php";
        $data['listTemplate']   = "{$dirName}.list.blade.php";
        $data['fields']         = $this->fields;
        $json                   = json_encode($data, JSON_PRETTY_PRINT);
        $this->result           = $json;

        return (bool)file_put_contents("$dirName/schema.json", $json);
    }

    public function getResult(): string
    {
        return $this->result;
    }
}
