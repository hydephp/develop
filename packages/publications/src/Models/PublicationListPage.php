<?php

declare(strict_types=1);

namespace Hyde\Publications\Models;

use Hyde\Pages\VirtualPage;
use Hyde\Publications\Actions\PublicationPageCompiler;

/**
 * @see \Hyde\Pages\PublicationPage
 * @see \Hyde\Framework\Testing\Feature\PublicationListPageTest
 */
class PublicationListPage extends VirtualPage
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
}
