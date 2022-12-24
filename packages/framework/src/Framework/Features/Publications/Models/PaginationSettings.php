<?php

declare(strict_types=1);

namespace Hyde\Framework\Features\Publications\Models;

use Hyde\Support\Concerns\Serializable;
use Hyde\Support\Contracts\SerializableContract;

class PaginationSettings implements SerializableContract
{
    use Serializable;

    public string $sortField = '__createdAt';
    public bool $sortAscending = true;
    /** @deprecated This setting might be deprecated as its unlikely one would enable page size limits without a way to traverse them */
    public bool $prevNextLinks = true;
    public int $pageSize = 25;

    public static function fromArray(array $data): static
    {
        return new static(...$data);
    }

    public function __construct(string $sortField = '__createdAt', bool $sortAscending = true, bool $prevNextLinks = true, int $pageSize = 25)
    {
        $this->sortField = $sortField;
        $this->sortAscending = $sortAscending;
        $this->prevNextLinks = $prevNextLinks;
        $this->pageSize = $pageSize;
    }

    public function toArray(): array
    {
        return [
            'sortField' => $this->sortField,
            'sortAscending' => $this->sortAscending,
            'prevNextLinks' => $this->prevNextLinks,
            'pageSize' => $this->pageSize,
        ];
    }
}
