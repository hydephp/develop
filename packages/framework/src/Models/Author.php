<?php

namespace Hyde\Framework\Models;

/**
 * The Post Author Object Model.
 */
class Author
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
     * The author's website URI.
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
    public function __construct(string $username, ?array $data = [])
    {
        $this->username = $username;
        if (isset($data['name'])) {
            $this->name = $data['name'];
        }
        if (isset($data['website'])) {
            $this->website = $data['website'];
        }
    }

    /**
     * Get the author's preferred name.
     *
     * @see \Hyde\Framework\Testing\Unit\AuthorGetNameTest
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name ?? $this->username;
    }
}
