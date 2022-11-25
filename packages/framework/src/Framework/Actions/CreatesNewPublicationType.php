<?php

declare(strict_types=1);

namespace Hyde\Framework\Actions;

use Hyde\Framework\Actions\Concerns\CreateAction;
use Hyde\Framework\Actions\Contracts\CreateActionContract;
use Hyde\Framework\Features\Publications\Models\PublicationType;
use Illuminate\Console\OutputStyle;
use Rgasch\Collection\Collection;

/**
 * Scaffold a new publication type schema.
 *
 * @see \Hyde\Console\Commands\MakePublicationCommand
 * @see \Hyde\Framework\Testing\Feature\Actions\CreatesNewPublicationTypeTest
 */
class CreatesNewPublicationType extends CreateAction implements CreateActionContract
{
    protected string $dirName;

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
        $this->dirName = $this->formatStringForStorage($this->name);
        $this->outputPath = "$this->dirName/schema.json";
    }

    protected function handleCreate(): void
    {
        $type = new PublicationType(
            $this->name,
            $this->canonicalField,
            $this->sortField,
            $this->sortDirection,
            $this->pageSize,
            $this->prevNextLinks,
            "{$this->dirName}_detail",
            "{$this->dirName}_list",
            $this->fields->toArray()
        );

        $this->output?->writeln("Saving publication data to [$this->outputPath]");

        $type->save($this->outputPath);

        // TODO: Generate the detail and list templates?
    }
}
