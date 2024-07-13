<?php

declare(strict_types=1);

namespace Hyde\Framework\Features\Blogging\DynamicPages;

use Hyde\Pages\InMemoryPage;
use Illuminate\Support\Collection;
use function Hyde\path_join;

/**
 * @experimental
 *
 * @codeCoverageIgnore This class is still experimental and not yet covered by tests.
 */
class PostAuthorsPage extends InMemoryPage
{
    /** @var \Illuminate\Support\Collection<\Hyde\Framework\Features\Blogging\Models\PostAuthor> */
    protected Collection $authors;

    public static string $outputDirectory = 'authors';

    public function __construct(Collection $authors)
    {
        $identifier = path_join(static::$outputDirectory, 'index');

        parent::__construct($identifier, [
            'authors' => $authors,
            'navigation' => [
                'visible' => false, // Todo: We could make this configurable
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
