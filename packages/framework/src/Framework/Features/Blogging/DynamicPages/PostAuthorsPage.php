<?php

declare(strict_types=1);

namespace Hyde\Framework\Features\Blogging\DynamicPages;

use Hyde\Pages\InMemoryPage;
use Illuminate\Support\Collection;

/**
 * @experimental
 */
class PostAuthorsPage extends InMemoryPage
{
    public function __construct(Collection $authors)
    {
        parent::__construct('authors');

        $this->authors = $authors;
    }
}
