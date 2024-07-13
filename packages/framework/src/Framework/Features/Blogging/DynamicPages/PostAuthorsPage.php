<?php

declare(strict_types=1);

namespace Hyde\Framework\Features\Blogging\DynamicPages;

use Hyde\Pages\InMemoryPage;
use Illuminate\Support\Collection;

/**
 * @experimental
 */
class PostAuthorsPage extends InMemoryPage
{
    /** @var \Illuminate\Support\Collection<\Hyde\Framework\Features\Blogging\Models\PostAuthor> */
    protected Collection $authors;

    public static string $sourceDirectory = 'authors';
    public static string $outputDirectory = 'authors';
    public static string $layout = 'hyde::pages.authors';

    public function __construct(Collection $authors)
    {
        parent::__construct('index', [
            'authors' => $authors,
            'navigation' => [
                'visible' => false, // Todo: We could make this configurable
            ],
        ]);

        $this->authors = $authors;
    }

    public function getBladeView(): string
    {
        return static::$layout;
    }
}
