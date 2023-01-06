<?php

declare(strict_types=1);

namespace Hyde\Framework\Features\Publications;

use Hyde\Framework\Features\Publications\Models\PublicationType;

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
}
