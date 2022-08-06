<?php

namespace Hyde\Framework\Actions\Constructors;

use Hyde\Framework\Models\Author;
use Hyde\Framework\Models\Pages\MarkdownPost;

/**
 * @internal
 */
class FindsAuthorForPost
{
    protected MarkdownPost $page;

    public static function run(MarkdownPost $page): Author|null
    {
        return (new static($page))->findAuthorForPost();
    }

    protected function __construct(MarkdownPost $page)
    {
        $this->page = $page;
    }

    protected function findAuthorForPost(): Author|null
    {
        if ($this->page->matter('author') !== null) {
            if (is_string($this->page->matter('author'))) {
                // If the author is a string, we assume it's a username,
                // so we'll try to find the author in the config
                return $this->findAuthor($this->page->matter('author'));
            }
            if (is_array($this->page->matter('author'))) {
                // If the author is an array, we'll assume it's a user
                // with one-off custom data, so we create a new author.
                // In the future we may want to merge config data with custom data
                return $this->createAuthor($this->page->matter('author'));
            }
        }

        return null;
    }

    protected function findAuthor(string $author): Author
    {
        return Author::get($author);
    }

    protected function createAuthor(array $data): Author
    {
        $username = $data['username'] ?? $data['name'] ?? 'Guest';

        return new Author($username, $data);
    }
}
