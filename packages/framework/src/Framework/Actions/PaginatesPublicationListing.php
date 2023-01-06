<?php

declare(strict_types=1);

namespace Hyde\Framework\Actions;

use Hyde\Framework\Features\Publications\Models\PublicationType;

class PaginatesPublicationListing
{
    protected PublicationType $type;

    public function __construct(PublicationType $type)
    {
        $this->type = $type;
    }

    public function __invoke(): array
    {
        return [];
    }
}
