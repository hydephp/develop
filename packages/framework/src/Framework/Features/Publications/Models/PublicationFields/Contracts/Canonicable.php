<?php

declare(strict_types=1);

namespace Hyde\Framework\Features\Publications\Models\PublicationFields\Contracts;

interface Canonicable
{
    public function getCanonicalValue(): string;
}
