<?php

declare(strict_types=1);

namespace Hyde\Framework\Models\Support;

use Illuminate\Support\Collection;

/**
 * The Post Author model object.
 *
 * @see \Hyde\Framework\Testing\Feature\AuthorTest
 */
class Author implements \Stringable
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
     * Could for example, be a Twitter page, website,
     * or a hyperlink to more posts by the author.
     *
     * @var string|null
     */
    public ?string $website = null;

    /**
     * Construct a new Author object.
     *
     * Parameters are supplied through an array to make it
     * easy to load data from Markdown post front matter.
     *
     * @param  string  $username
     * @param  array|null  $data
     */
    final public function __construct(string $username, ?array $data = [])
    {
        $this->username = $username;

        if (isset($data['name'])) {
            $this->name = $data['name'];
        }

        if (isset($data['website'])) {
            $this->website = $data['website'];
        }
    }

    /** Dynamically get or create an author based on a username string or front matter array */
    final public static function make(string|array $data): static
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
        return new static($username, ['name' => $name, 'website' => $website]);
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
