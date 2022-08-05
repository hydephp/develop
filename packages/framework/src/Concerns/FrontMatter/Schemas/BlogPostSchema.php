<?php

namespace Hyde\Framework\Concerns\FrontMatter\Schemas;

use Hyde\Framework\Helpers\Author as AuthorHelper;
use Hyde\Framework\Models\Author;
use Hyde\Framework\Models\DateString;
use Hyde\Framework\Models\Image;

trait BlogPostSchema
{
    /** @example "My New Post" */
    public string $title;

    /** @example "A short description" */
    public ?string $description = null;

    /** @example "general", "my favorite recipes" */
    public ?string $category = null;

    /**
     * The date the post was published.
     *
     * @example 'YYYY-MM-DD [HH:MM]' (Must be parsable by `strtotime()`)
     * @yamlType string|optional
     */
    public ?DateString $date = null;

    /**
     * @example See author section
     * @yamlType string|array|optional
     */
    public ?Author $author = null;

    /**
     * @example See image section
     * @yamlType string|array|optional
     */
    public ?Image $image = null;

    protected function constructBlogPostSchema(): void
    {
        $this->category = $this->matter('category');
        $this->description = $this->matter('description', substr($this->markdown, 0, 125) . '...');
        $this->constructDateString();
        $this->constructAuthor();
        $this->constructImage();
    }

    private function constructDateString(): void
    {
        if ($this->matter('date') !== null) {
            $this->date = new DateString($this->matter('date'));
        }
    }

    private function constructAuthor(): void
    {
        if ($this->matter('author') !== null) {
            if (is_string($this->matter('author'))) {
                // If the author is a string, we assume it's a username,
                // so we'll try to find the author in the config
                $this->author = $this->findAuthor($this->matter('author'));
            }
            if (is_array($this->matter('author'))) {
                // If the author is an array, we'll assume it's a user
                // with one-off custom data, so we create a new author.
                // In the future we may want to merge config data with custom data
                $this->author = $this->createAuthor($this->matter('author'));
            }
        }
    }

    private function findAuthor(string $author): Author
    {
        return AuthorHelper::get($author);
    }

    private function createAuthor(array $data): Author
    {
        $username = $data['username'] ?? $data['name'] ?? 'Guest';

        return new Author($username, $data);
    }

    private function constructImage(): void
    {
        if ($this->matter('image') !== null) {
            if (is_string($this->matter('image'))) {
                $this->image = $this->constructBaseImage($this->matter('image'));
            }
            if (is_array($this->matter('image'))) {
                $this->image = $this->constructFullImage($this->matter('image'));
            }
        }
    }

    private function constructBaseImage(string $image): Image
    {
        if (str_starts_with($image, 'http')) {
            return new Image([
                'uri' => $image,
            ]);
        }

        return new Image([
            'path' => $image,
        ]);
    }

    private function constructFullImage(array $image): Image
    {
        return new Image($image);
    }
}
