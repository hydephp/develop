<?php

namespace Hyde\Framework\Concerns;

use Hyde\Framework\Helpers\Author as AuthorHelper;
use Hyde\Framework\Models\Author;

/**
 * Handle logic for Page models that have an Author.
 *
 * @see \Hyde\Framework\Models\Author
 * @see \Hyde\Framework\Testing\Unit\HasAuthorTest
 */
trait HasAuthor
{
    public Author $author;

    public function constructAuthor(): void
    {
        if (isset($this->matter['author'])) {
            if (is_string($this->matter['author'])) {
                // If the author is a string, we assume it's a username,
                // so we'll try to find the author in the config
                $this->author = $this->findAuthor($this->matter['author']);
            }
            if (is_array($this->matter['author'])) {
                // If the author is an array, we'll assume it's a user
                // with one-off custom data, so we create a new author.
                // In the future we may want to merge config data with custom data
                $this->author = $this->createAuthor($this->matter['author']);
            }
        }
    }

    protected function findAuthor(string $author): Author
    {
        return AuthorHelper::get($author);
    }

    protected function createAuthor(array $data): Author
    {
        $username = $data['username'] ?? $data['name'] ?? 'Guest';

        return new Author($username, $data);
    }
}
