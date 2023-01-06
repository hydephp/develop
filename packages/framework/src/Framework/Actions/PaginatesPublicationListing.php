<?php

declare(strict_types=1);

namespace Hyde\Framework\Actions;

use Hyde\Framework\Features\Publications\Models\PublicationListPage;
use Hyde\Framework\Features\Publications\Models\PublicationType;
use Hyde\Framework\Features\Publications\PublicationService;
use Hyde\Hyde;
use Hyde\Pages\VirtualPage;

use function array_merge;
use function file_put_contents;

class PaginatesPublicationListing
{
    protected PublicationType $type;

    /** fixme: @var array<\Hyde\Framework\Features\Publications\Models\PublicationListPage> */
    protected array $pages;

    public function __construct(PublicationType $type)
    {
        $this->type = $type;
    }

    public function __invoke(): array
    {
        $this->generatePaginationPages();

        return $this->pages;
    }

    protected function generatePaginationPages(): void
    {
        $pages = PublicationService::getPublicationsForPubType($this->type);

        $count = 25; //FIXme get form type
        $chunks = $pages->chunk($count);

        foreach ($chunks as $index => $chunk) {
            $page = $index + 1;
            $data = [
                'publications' => $chunk,
                'pagination' => [
                    'current' => $page,
                    'total' => $chunks->count(),
                    'next' => $page < $chunks->count() ? $page + 1 : null,
                    'previous' => $page > 1 ? $page - 1 : null,
                    'offset' => $index * $count + 1,
                ],
            ];

            $this->pages[] = $this->makePaginationPage($this->type, $page, $data);;
        }
    }

    protected function makePaginationPage(PublicationType $pubType, int $pageNumber, array $data): PublicationListPage
    {
        $identifier = "{$pubType->getDirectory()}/page-$pageNumber";

        $path = "$identifier.html";
        $data = array_merge([
            'type' => $pubType,
            'paginator' => (object) $data['pagination'],
        ], $data);

        $page = new VirtualPage($identifier, array_merge([
            'title' => $pubType->name." (Page - $pageNumber)",
        ], $data), '', 'hyde::layouts.publication_paginated_list');

        return $page;
    }
}
