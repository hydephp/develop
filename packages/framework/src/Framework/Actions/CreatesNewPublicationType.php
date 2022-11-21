<?php

declare(strict_types=1);

namespace Hyde\Framework\Actions;

use Hyde\Framework\Actions\Interfaces\CreateActionInterface;
use Hyde\Framework\Features\Publications\Models\PublicationType;
use Hyde\Framework\Features\Publications\PublicationHelper;
use Hyde\Hyde;
use Illuminate\Console\OutputStyle;
use Rgasch\Collection\Collection;
use function sprintf;

/**
 * Scaffold a new publication type schema.
 *
 * @see \Hyde\Framework\Testing\Feature\Actions\CreatesNewPublicationTypeSchemaTest
 */
class CreatesNewPublicationType implements CreateActionInterface
{
    protected string $result;

    public function __construct(
        protected string $name,
        protected Collection $fields,
        protected string $canonicalField,
        protected string $sortField,
        protected string $sortDirection,
        protected int $pageSize,
        protected bool $prevNextLinks,
        protected ?OutputStyle $output = null,
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

        $this->output?->writeln(sprintf('Saving publication data to [%s]', Hyde::pathToRelative($outFile)));

        $type->save($outFile);
        $this->result = $type->toJson();

        // TODO: Generate the detail and list templates?
    }

    public function getResult(): string
    {
        return $this->result;
    }
}
