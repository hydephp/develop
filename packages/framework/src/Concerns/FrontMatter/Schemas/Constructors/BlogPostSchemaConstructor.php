<?php

namespace Hyde\Framework\Concerns\FrontMatter\Schemas\Constructors;

use Hyde\Framework\Models\Author;
use Hyde\Framework\Models\DateString;
use Hyde\Framework\Models\Image;

trait BlogPostSchemaConstructor
{
    protected function constructBlogPostSchema(): void
    {
        $this->category = $this->matter('category');
        $this->description = $this->matter('description', $this->makeDescription());
        $this->date = $this->matter('date') !== null ? new DateString($this->matter('date')) : null;
        $this->author = $this->getAuthor();
        $this->image = $this->getImage();
    }

    protected function makeDescription(): string
    {
        if (strlen($this->markdown) >= 128) {
            return substr($this->markdown, 0, 125).'...';
        }

        return (string) $this->markdown;
    }

    protected function getAuthor(): ?Author
    {
        if ($this->matter('author')) {
            return Author::make($this->matter('author'));
        }

        return null;
    }

    protected function getImage(): ?Image
    {
        if ($this->matter('image')) {
            return Image::make($this->matter('image'));
        }

        return null;
    }
}
