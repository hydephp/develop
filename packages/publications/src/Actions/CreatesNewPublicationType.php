<?php

declare(strict_types=1);

namespace Hyde\Publications\Actions;

use Hyde\Hyde;
use Hyde\Publications\Models\PublicationType;
use Illuminate\Contracts\Support\Arrayable;

/**
 * Scaffold a new publication type schema.
 *
 * @see \Hyde\Publications\Commands\MakePublicationCommand
 * @see \Hyde\Publications\Testing\Feature\CreatesNewPublicationTypeTest
 */
class CreatesNewPublicationType extends CreateAction
{
    protected string $directoryName;

    public function __construct(
        protected string $name,
        protected Arrayable $fields,
        protected ?string $canonicalField = null,
        protected ?string $sortField = null,
        protected ?bool $sortAscending = null,
        protected ?int $pageSize = null,
    ) {
        $this->directoryName = $this->formatStringForStorage($this->name);
        $this->outputPath = "$this->directoryName/schema.json";
    }

    protected function handleCreate(): void
    {
        (new PublicationType(
            $this->name,
            $this->canonicalField ?? '__createdAt',
            'detail.blade.php',
            'list.blade.php',
            $this->sortField ?? '__createdAt',
            $this->sortAscending ?? true,
            $this->pageSize ?? 0,
            $this->fields->toArray()
        ))->save($this->outputPath);

        $this->createDetailTemplate();
        $this->createListTemplate();
    }

    protected function createDetailTemplate(): void
    {
        $this->publishPublicationFile('detail', 'detail');
    }

    protected function createListTemplate(): void
    {
        $this->publishPublicationFile('list', $this->usesPagination() ? 'paginated_list' : 'list');
    }

    protected function publishPublicationFile(string $filename, string $viewName): void
    {
        copy(Hyde::vendorPath("/../publications/resources/views/$viewName.blade.php"), Hyde::path("$this->directoryName/$filename.blade.php"));
    }

    protected function usesPagination(): bool
    {
        return $this->pageSize > 0;
    }
}
