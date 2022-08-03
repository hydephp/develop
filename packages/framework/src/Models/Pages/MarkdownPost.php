<?php

namespace Hyde\Framework\Models\Pages;

use Hyde\Framework\Concerns\HasArticleMetadata;
use Hyde\Framework\Concerns\HasAuthor;
use Hyde\Framework\Concerns\HasDateString;
use Hyde\Framework\Concerns\HasFeaturedImage;
use Hyde\Framework\Contracts\AbstractMarkdownPage;
use Hyde\Framework\Hyde;
use Illuminate\Support\Collection;

class MarkdownPost extends AbstractMarkdownPage
{
    use HasAuthor;
    use HasArticleMetadata;
    use HasDateString;
    use HasFeaturedImage;

    public ?string $category;

    public static string $sourceDirectory = '_posts';
    public static string $outputDirectory = 'posts';
    public static string $template = 'hyde::layouts/post';

    public function __construct(array $matter = [], string $body = '', string $title = '', string $identifier = '')
    {
        parent::__construct($matter, $body, $title, $identifier);

        $this->constructAuthor();
        $this->constructMetadata();
        $this->constructDateString();
        $this->constructFeaturedImage();

        $this->category = $this->matter['category'] ?? null;
    }

    public function getCanonicalLink(): string
    {
        return Hyde::url($this->getCurrentPagePath().'.html');
    }

    public function getPostDescription(): string
    {
        return $this->matter['description'] ?? substr($this->body, 0, 125).'...';
    }

    public static function getLatestPosts(): Collection
    {
        return static::all()->sortByDesc('matter.date');
    }
}
