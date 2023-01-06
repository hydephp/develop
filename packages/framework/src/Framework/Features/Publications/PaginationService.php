<?php

declare(strict_types=1);

namespace Hyde\Framework\Features\Publications;

use Hyde\Framework\Features\Publications\Models\PublicationType;
use Illuminate\Support\Collection;

/**
 * @see \Hyde\Framework\Testing\Feature\PaginationServiceTest
 *
 * Internal developer note: It may be useful to match the Laravel method names as much as possible.
 * @link https://laravel.com/docs/9.x/pagination#paginator-instance-methods
 */
class PaginationService
{
    protected PublicationType $publicationType;

    protected Collection $chunks;

    public function __construct(PublicationType $publicationType)
    {
        $this->publicationType = $publicationType;
    }

    public function generate(): static
    {
        $this->chunks = PublicationService::getPublicationsForPubType($this->publicationType)
            ->chunk($this->publicationType->pagination->pageSize);

        return $this;
    }

    public function getPaginatedPageCollection(): Collection
    {
        return $this->chunks;
    }
}
