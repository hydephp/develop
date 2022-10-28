<?php

declare(strict_types=1);

namespace Hyde\Facades;

use Hyde\Framework\Features\Blogging\Models\PostAuthor;

/**
 * Allows you to easily add pre-defined authors for your blog posts.
 *
 * @see \Hyde\Framework\Features\Blogging\Models\PostAuthor
 */
class Author
{
    /**
     * Construct a new Post Author. For Hyde to discover this author,
     * you must call this method from your hyde.php config file.
     *
     * @see https://hydephp.com/docs/master/customization.html#authors
     *
     * @param string $username  The username of the author. This is the key used to find authors in the config.
     * @param string|null $name The optional display name of the author, leave blank to use the username.
     * @param string|null $website The author's optional website URL. Website, Twitter, etc.
     *
     * @return \Hyde\Framework\Features\Blogging\Models\PostAuthor
     */
    public static function create(string $username, ?string $name = null, ?string $website = null): PostAuthor
    {
        return new PostAuthor($username, $name, $website);
    }

    /**
     * Get a Post Author instance from the config. If no author matching the username is found,
     * a new Post Author instance will be created with just username supplied to the method.
     *
     * @param string $username
     * @return \Hyde\Framework\Features\Blogging\Models\PostAuthor
     */
    public static function get(string $username): PostAuthor
    {
        return PostAuthor::get($username);
    }
}
