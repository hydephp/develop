<?php

declare(strict_types=1);

namespace Hyde\Framework\Features\Publications\Models;

use Hyde\Pages\BladePage;

class PublicationListPage extends BladePage
{
    public static string $sourceDirectory = '%type%';
    public static string $outputDirectory = '%type%';
    public static string $fileExtension = '';

    public PublicationType $type;

    public function __construct(PublicationType $type)
    {
        parent::__construct("{$type->getDirectory()}/index");
        $this->type = $type;
    }
}
