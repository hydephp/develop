<?php

namespace Hyde\Framework\Actions\Constructors;

use Hyde\Framework\Models\Image;
use Hyde\Framework\Models\Pages\MarkdownPost;

/**
 * @internal
 * @see \Hyde\Framework\Testing\Unit\ConfiguresFeaturedImageForPostTest
 */
class ConfiguresFeaturedImageForPost
{
    protected MarkdownPost $page;

    public static function run(MarkdownPost $page): Image|null
    {
        return (new static($page))->constructImage();
    }

    protected function __construct(MarkdownPost $page)
    {
        $this->page = $page;
    }

    private function constructImage(): Image|null
    {
        if ($this->page->matter('image') !== null) {
            if (is_string($this->page->matter('image'))) {
                return $this->constructBaseImage($this->page->matter('image'));
            }
            if (is_array($this->page->matter('image'))) {
                return $this->constructFullImage($this->page->matter('image'));
            }
        }

        return null;
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
