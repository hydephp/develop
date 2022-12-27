<?php

declare(strict_types=1);

namespace Hyde\Framework\Actions;

use function file_put_contents;
use Hyde\Framework\Actions\Concerns\CreateAction;
use Hyde\Framework\Actions\Contracts\CreateActionContract;
use Hyde\Framework\Features\Publications\Models\PublicationType;
use Hyde\Hyde;
use Illuminate\Support\Collection;

/**
 * Scaffold a new publication type schema.
 *
 * @see \Hyde\Console\Commands\MakePublicationCommand
 * @see \Hyde\Framework\Testing\Feature\Actions\CreatesNewPublicationTypeTest
 */
class CreatesNewPublicationType extends CreateAction implements CreateActionContract
{
    protected string $directoryName;

    public function __construct(
        protected string $name,
        protected Collection $fields,
        protected string $canonicalField,
        protected ?string $sortField = null,
        protected ?bool $sortAscending = null,
        protected ?bool $prevNextLinks = null,
        protected ?int $pageSize = null,
    ) {
        $this->directoryName = $this->formatStringForStorage($this->name);
        $this->outputPath = "$this->directoryName/schema.json";
    }

    protected function handleCreate(): void
    {
        $type = new PublicationType(
            $this->name,
            $this->canonicalField,
            $this->detailTemplateName(),
            $this->listTemplateName(),
            [
                $this->sortField ?? '__createdAt',
                $this->sortAscending ?? true,
                $this->prevNextLinks ?? true,
                $this->pageSize ?? 25,
            ],
            $this->fields->toArray()
        );

        $type->save($this->outputPath);

        $this->createDetailTemplate();
        $this->createListTemplate();
    }

    protected function detailTemplateName(): string
    {
        return "{$this->directoryName}_detail";
    }

    protected function listTemplateName(): string
    {
        return "{$this->directoryName}_list";
    }

    protected function createDetailTemplate(): void
    {
        $contents = $this->stubContents('// detail');

        $this->savePublicationFile("{$this->detailTemplateName()}.blade.php", $contents);
    }

    protected function createListTemplate(): void
    {
        $contents = $this->stubContents('// list');

        $this->savePublicationFile("{$this->listTemplateName()}.blade.php", $contents);
    }

    protected function savePublicationFile(string $filename, string $contents): int
    {
        return file_put_contents(Hyde::path("$this->directoryName/$filename"), "$contents\n");
    }

    protected function stubContents(string $slot): string
    {
        return <<<BLADE
        @extends('hyde::layouts.app')
        @section('content')
        
            <main id="content" class="mx-auto max-w-7xl py-16 px-8">
                $slot
            </main>
        
        @endsection
        BLADE;
    }
}
