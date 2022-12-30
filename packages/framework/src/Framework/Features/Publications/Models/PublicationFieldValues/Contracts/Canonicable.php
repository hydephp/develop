<?php

declare(strict_types=1);

namespace Hyde\Framework\Features\Publications\Models\PublicationFieldValues\Contracts;

use Stringable;

interface Canonicable extends Stringable
{
    public function getCanonicalValue(): string;
}
