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
    public int $pageSize = 25;
    public bool $prevNextLinks = true;

    public static function fromArray(array $data): static
    {
        return new static(...$data);
    }

    public function __construct(string $sortField = '__createdAt', bool $sortAscending = true, int $pageSize = 25, bool $prevNextLinks = true)
    {
        $this->sortField     = $sortField;
        $this->sortAscending = $sortAscending;
        $this->pageSize      = $pageSize;
        $this->prevNextLinks = $prevNextLinks;
    }

    public function toArray(): array
    {
        return [
            'sortField' => $this->sortField,
            'sortAscending' => $this->sortAscending,
            'pageSize' => $this->pageSize,
            'prevNextLinks' => $this->prevNextLinks,
        ];
    }
}
