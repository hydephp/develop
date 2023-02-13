<?php

declare(strict_types=1);

namespace Hyde\Framework\Factories;

use Hyde\Framework\Concerns\InteractsWithFrontMatter;
use Hyde\Framework\Factories\Concerns\CoreDataObject;
use Hyde\Framework\Features\Blogging\Models\FeaturedImage;
use Hyde\Framework\Features\Blogging\Models\PostAuthor;
use Hyde\Markdown\Contracts\FrontMatter\BlogPostSchema;
use Hyde\Markdown\Models\FrontMatter;
use Hyde\Markdown\Models\Markdown;
use Hyde\Support\Models\DateString;
use function strlen;
use function substr;

/**
 * Streamlines the data construction specific to a blog post.
 *
 * Simply pass along the data the class needs to run, then access the data using the toArray() method.
 *
 * All data can be set using front matter in the page source file. If no front matter is set for the given key,
 * this class will attempt to generate and discover the values based on the page and the project's configuration.
 */
class BlogPostDataFactory extends Concerns\PageDataFactory implements BlogPostSchema
{
    use InteractsWithFrontMatter;

    /**
     * The front matter properties supported by this factory.
     *
     * Note that this class does not add the title, as that is already added to all pages.
     */
    final public const SCHEMA = BlogPostSchema::MARKDOWN_POST_SCHEMA;

    private readonly FrontMatter $matter;
    private readonly Markdown $markdown;

    protected readonly ?string $description;
    protected readonly ?string $category;
    protected readonly ?DateString $date;
    protected readonly ?PostAuthor $author;
    protected readonly ?FeaturedImage $image;

    public function __construct(CoreDataObject $pageData)
    {
        $this->matter = $pageData->matter;
        $this->markdown = $pageData->markdown;

        $this->description = $this->makeDescription();
        $this->category = $this->makeCategory();
        $this->date = $this->makeDate();
        $this->author = $this->makeAuthor();
        $this->image = $this->makeImage();
    }

    /**
     * @return array{description: string|null, category: string|null, date: \Hyde\Support\Models\DateString|null, author: \Hyde\Framework\Features\Blogging\Models\PostAuthor|null, image: \Hyde\Framework\Features\Blogging\Models\FeaturedImage|null}
     */
    public function toArray(): array
    {
        return [
            'description' => $this->description,
            'category' => $this->category,
            'date' => $this->date,
            'author' => $this->author,
            'image' => $this->image,
        ];
    }

    protected function makeDescription(): string
    {
        return $this->matter('description') ?? $this->getTruncatedMarkdown($this->markdown->body());
    }

    protected function makeCategory(): ?string
    {
        return $this->matter('category');
    }

    protected function makeDate(): ?DateString
    {
        if ($this->matter('date')) {
            return new DateString($this->matter('date'));
        }

        return null;
    }

    protected function makeAuthor(): ?PostAuthor
    {
        if ($this->matter('author')) {
            return PostAuthor::make($this->matter('author'));
        }

        return null;
    }

    protected function makeImage(): ?FeaturedImage
    {
        if ($this->matter('image')) {
            return FeaturedImageFactory::make($this->matter);
        }

        return null;
    }

    private function getTruncatedMarkdown(string $markdown): string
    {
        if (strlen($markdown) >= 128) {
            return substr($markdown, 0, 125).'...';
        }

        return $markdown;
    }
}
