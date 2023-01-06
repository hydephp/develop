<?php

declare(strict_types=1);

namespace Hyde\Framework\Features\Publications;

use Hyde\Framework\Features\Publications\Models\PublicationType;
use Illuminate\Support\Collection;

/**
 * @see \Hyde\Framework\Testing\Feature\PaginationServiceTest
 */
class PaginationService
{
    protected PublicationType $publicationType;

    public function __construct(PublicationType $publicationType)
    {
        $this->publicationType = $publicationType;
    }

    public function getPaginatedPageCollection(): Collection
    {
        return PublicationService::getPublicationsForPubType($this->publicationType)
                   ->chunk($this->publicationType->pagination->pageSize);
    }
}
