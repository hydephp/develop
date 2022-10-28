<?php

declare(strict_types=1);

namespace Hyde\Facades;

use Hyde\Framework\Features\Blogging\Models\PostAuthor;

class Author
{
    public static function create(string $username, ?string $name = null, ?string $website = null): PostAuthor
    {
        return new PostAuthor($username, ['name' => $name, 'website' => $website]);
    }
}
