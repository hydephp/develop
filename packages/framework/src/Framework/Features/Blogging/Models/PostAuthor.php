<?php

declare(strict_types=1);

namespace Hyde\Framework\Features\Blogging\Models;

use Illuminate\Support\Collection;
use Stringable;

/**
 * The Post Author model object.
 *
 * @see \Hyde\Framework\Testing\Feature\PostAuthorTest
 */
class PostAuthor implements Stringable
{
    /**
     * The username of the author.
     * This is the key used to find authors in the config.
     *
     * @var string
     */
    public string $username;

    /**
     * The display name of the author.
     *
     * @var string|null
     */
    public ?string $name = null;

    /**
     * The author's website URL.
     *
     * Could for example, be a Twitter page, website, or a hyperlink to more posts by the author.
     * Should be a fully qualified link, meaning it starts with http:// or https://.
     *
     * @var string|null
     */
    public ?string $website = null;

    /**
     * Construct a new Post Author object.
     *
     * If your input is in the form of an array, you may rather want to use the `make` method.
     *
     * @param string $username
     * @param string|null $name
     * @param string|null $website
     */
    public function __construct(string $username, ?string $name = null, ?string $website = null)
    {
        $this->username = $username;
        $this->name = $name;
        $this->website = $website;
    }

    /** Dynamically get or create an author based on a username string or front matter array */
    public static function make(string|array $data): static
    {
        if (is_string($data)) {
            return static::get($data);
        }

        return static::create(static::findUsername($data), $data['name'] ?? null, $data['website'] ?? null);
    }

    public static function get(string $username): static
    {
        return static::all()->firstWhere('username', $username) ?? static::create($username);
    }

    public static function all(): Collection
    {
        return new Collection(config('authors', []));
    }

    public static function create(string $username, ?string $name = null, ?string $website = null): static
    {
        return new static($username, $name, $website);
    }

    public function __toString(): string
    {
        return $this->getName();
    }

    public function getName(): string
    {
        return $this->name ?? $this->username;
    }

    protected static function findUsername(array $data): string
    {
        return $data['username'] ?? $data['name'] ?? 'Guest';
    }
}
