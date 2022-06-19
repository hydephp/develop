<?php

namespace Hyde\Framework\Helpers;

use Hyde\Framework\Models\Author as AuthorModel;
use Illuminate\Support\Collection;

/**
 * @see \Hyde\Framework\Testing\Feature\AuthorHelperTest
 */
class Author
{
    public static function create(string $username, ?string $name = null, ?string $website = null): AuthorModel
    {
        return new AuthorModel($username, [
            'name' => $name,
            'website'=> $website,
        ]);
    }

    public static function all(): Collection
    {
        return new Collection(config('authors', []));
    }

    public static function get(string $username): AuthorModel
    {
        return static::all()->firstWhere('username', $username)
            ?? static::create($username);
    }
}
