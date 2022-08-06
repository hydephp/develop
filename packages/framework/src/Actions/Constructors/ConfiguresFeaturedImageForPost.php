<?php

namespace Hyde\Framework\Actions\Constructors;

use Hyde\Framework\Models\Image;
use Hyde\Framework\Models\Pages\MarkdownPost;

/**
 * @internal
 *
 * @see \Hyde\Framework\Testing\Unit\ConfiguresFeaturedImageForPostTest
 */
class ConfiguresFeaturedImageForPost
{
    public static function run(MarkdownPost $page): Image|null
    {
        return (new static($page))->constructImage();
    }

    protected function __construct(protected MarkdownPost $page)
    {
    }

    private function constructImage(): Image|null
    {
        if ($this->page->matter->get('image') !== null) {
            if (is_string($this->page->matter->get('image'))) {
                return $this->constructBaseImage($this->page->matter->get('image'));
            }
            if (is_array($this->page->matter->get('image'))) {
                return new Image($this->page->matter->get('image'));
            }
        }

        return null;
    }

    private function constructBaseImage(string $image): Image
    {
        return str_starts_with($image, 'http')
            ? new Image(['uri' => $image])
            : new Image(['path' => $image]);
    }
}
