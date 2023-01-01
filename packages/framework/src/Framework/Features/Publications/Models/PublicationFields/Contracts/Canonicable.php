<?php

declare(strict_types=1);

namespace Hyde\Framework\Features\Publications\Models\PublicationFields\Contracts;

use Stringable;

interface Canonicable extends Stringable
{
    public function getCanonicalValue(): string;
}
