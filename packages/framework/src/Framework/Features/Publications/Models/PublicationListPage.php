<?php

declare(strict_types=1);

namespace Hyde\Framework\Features\Publications\Models;

use Hyde\Pages\BladePage;

class PublicationListPage extends BladePage
{
    public static string $sourceDirectory = '%type%';
    public static string $outputDirectory = '';
    public static string $fileExtension = 'json';

    public PublicationType $type;

    public function __construct(PublicationType $type)
    {
        parent::__construct("{$type->getDirectory()}/index");
        $this->type = $type;
    }
}
