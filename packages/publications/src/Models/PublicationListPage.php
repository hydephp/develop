<?php

declare(strict_types=1);

namespace Hyde\Publications\Models;

use Hyde\Pages\InMemoryPage;
use Hyde\Publications\Actions\PublicationPageCompiler;

use function config;
use function in_array;

/**
 * @see \Hyde\Publications\Models\PublicationPage
 * @see \Hyde\Publications\Testing\Feature\PublicationListPageTest
 */
class PublicationListPage extends InMemoryPage
{
    public PublicationType $type;

    public function __construct(PublicationType $type)
    {
        $this->type = $type;

        parent::__construct("{$type->getDirectory()}/index", [
            'title' => $this->type->name,
        ], view: $type->listTemplate);
    }

    public function compile(): string
    {
        return PublicationPageCompiler::call($this);
    }

    public function showInNavigation(): bool
    {
        return ! in_array($this->type->getDirectory(), config('hyde.navigation.exclude', []));
    }
}
