<?php

declare(strict_types=1);

namespace Hyde\Framework\Actions;

use Hyde\Framework\Actions\Interfaces\CreateActionInterface;
use Hyde\Framework\Concerns\InteractsWithDirectories;
use Hyde\Framework\Features\Publications\Models\PublicationType;
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

        $type = new PublicationType(
            $this->name,
            $this->canonicalField,
            $this->sortField,
            $this->sortDirection,
            $this->pageSize,
            $this->prevNextLinks,
            "{$dirName}_detail",
            "{$dirName}_list",
            $this->fields->toArray()
        );

        $this->result = $type->toJson();

        echo sprintf("Saving publicationType data to [%s]\n", Hyde::pathToRelative($outFile));

        $type->save($outFile);
    }

    public function getResult(): string
    {
        return $this->result;
    }
}
