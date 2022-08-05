<?php

namespace Hyde\Framework\Concerns;

use Hyde\Framework\Models\Image;

/**
 * Handle logic for Page models that have a Featured Image.
 *
 * @see \Hyde\Framework\Models\Image
 */
trait HasFeaturedImage
{
    public function constructFeaturedImage(): void
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

    public function constructBaseImage(string $image): Image
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

    public function constructFullImage(array $image): Image
    {
        return new Image($image);
    }
}
