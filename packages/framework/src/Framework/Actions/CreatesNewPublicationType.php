<?php

declare(strict_types=1);

namespace Hyde\Framework\Actions;

use Hyde\Framework\Actions\Interfaces\CreateActionInterface;
use Hyde\Framework\Concerns\InteractsWithDirectories;
use Hyde\Framework\Features\Publications\PublicationHelper;
use Hyde\Hyde;
use Rgasch\Collection\Collection;
use function Safe\file_put_contents;
use function Safe\json_encode;
use function Safe\mkdir;

/**
 * Scaffold a new publication type schema.
 *
 * @see \Hyde\Framework\Testing\Feature\Actions\CreatesNewPublicationTypeSchemaTest
 */
class CreatesNewPublicationType implements CreateActionInterface
{
    use InteractsWithDirectories;

    protected string $result;

    public function __construct(
        protected string $name,
        protected Collection $fields,
        protected string $canonicalField,
        protected string $sortField,
        protected string $sortDirection,
        protected int $pageSize,
        protected bool $prevNextLinks
    ) {
    }

    public function create(): void
    {
        $dirName = PublicationHelper::formatNameForStorage($this->name);
        $outFile = Hyde::path("$dirName/schema.json");
        mkdir($dirName);

        $data = [];
        $data['name'] = $this->name;
        $data['canonicalField'] = $this->canonicalField;
        $data['sortField'] = $this->sortField;
        $data['sortDirection'] = $this->sortDirection;
        $data['pageSize'] = $this->pageSize;
        $data['prevNextLinks'] = $this->prevNextLinks;
        $data['detailTemplate'] = "{$dirName}_detail";
        $data['listTemplate'] = "{$dirName}_list";
        $data['fields'] = $this->fields;
        $json = json_encode($data, JSON_PRETTY_PRINT);
        $this->result = $json;

        echo sprintf("Saving publicationType data to [%s]\n", Hyde::pathToRelative($outFile));

        file_put_contents($outFile, $json);
    }

    public function getResult(): string
    {
        return $this->result;
    }
}
