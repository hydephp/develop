<?php

declare(strict_types=1);

namespace Hyde\Framework\Features\Publications\Models;

use Hyde\Framework\Features\Publications\PublicationHelper;
use Hyde\Pages\BladePage;
use function view;

/**
 * @see \Hyde\Pages\PublicationPage
 * @see \Hyde\Framework\Testing\Feature\PublicationListPageTest
 */
class PublicationListPage extends BladePage
{
    public static string $sourceDirectory = '__publications';
    public static string $outputDirectory = '';
    public static string $fileExtension = 'json';

    public PublicationType $type;

    public function __construct(PublicationType $type)
    {
        $this->type = $type;

        parent::__construct("{$type->getDirectory()}/index");
    }

    public function compile(): string
    {
        return view("pubtypes.{$this->type->getSchema()['listTemplate']}", [
            'publications' => PublicationHelper::getPublicationsForPubType($this->type),
        ])->render();
    }

    public function getSourcePath(): string
    {
        return $this->type->getDirectory().'/schema.json';
    }
}
