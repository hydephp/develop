<?php

declare(strict_types=1);

namespace Hyde\Framework\Features\Blogging\DynamicPages;

use Hyde\Pages\InMemoryPage;
use Illuminate\Support\Collection;
use Hyde\Framework\Features\Blogging\DynamicBlogPostPageHelper;

/**
 * @experimental
 *
 * @codeCoverageIgnore This class is still experimental and not yet covered by tests.
 */
class PostAuthorsPage extends InMemoryPage
{
    /** @var \Illuminate\Support\Collection<\Hyde\Framework\Features\Blogging\Models\PostAuthor> */
    protected Collection $authors;

    public function __construct(Collection $authors)
    {
        parent::__construct(DynamicBlogPostPageHelper::authorBaseRouteKey(), [
            'authors' => $authors,
            'navigation' => [
                'visible' => false,
            ],
        ]);

        $this->authors = $authors;
    }

    public function getBladeView(): string
    {
        // Todo: Support/document overriding the view

        return 'hyde::pages.authors';
    }
}
