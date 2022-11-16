<?php

declare(strict_types=1);

namespace Hyde\Framework\Actions;

use Hyde\Framework\Actions\Interfaces\CreateActionInterface;
use Hyde\Framework\Concerns\InteractsWithDirectories;
use Hyde\HydeHelper;
use Rgasch\Collection\Collection;

use function Safe\file_put_contents;
use function Safe\json_encode;
use function Safe\mkdir;

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
        protected int $pagesize,
        protected bool $prevNextLinks
    ) {
    }


    public function create(): void
    {
        $dirName = HydeHelper::formatNameForStorage($this->name);
        $outFile = "$dirName/schema.json";
        mkdir($dirName);

        $data                   = [];
        $data['name']           = $this->name;
        $data['canonicalField'] = $this->canonicalField;
        $data['sortField']      = $this->sortField;
        $data['sortDirection']  = $this->sortDirection;
        $data['pagesize']       = $this->pagesize;
        $data['prevNextLinks']  = $this->prevNextLinks;
        $data['detailTemplate'] = "{$dirName}_detail.blade.php";
        $data['listTemplate']   = "{$dirName}_list.blade.php";
        $data['fields']         = $this->fields;
        $json                   = json_encode($data, JSON_PRETTY_PRINT);
        $this->result           = $json;

        print "Saving publicationType data to [$outFile]\n";

        file_put_contents($outFile, $json);
    }

    public function getResult(): string
    {
        return $this->result;
    }
}
