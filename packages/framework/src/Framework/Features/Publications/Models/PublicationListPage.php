<?php

declare(strict_types=1);

namespace Hyde\Framework\Features\Publications\Models;

use Hyde\Framework\Actions\PublicationPageCompiler;
use Hyde\Pages\VirtualPage;

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

        parent::__construct("{$type->getDirectory()}/index", view: $type->listTemplate);
    }

    public function compile(): string
    {
        return PublicationPageCompiler::call($this);
    }

    public function htmlTitle(): string
    {
        return config('site.name', 'HydePHP').' - '.$this->type->name;
    }
}
