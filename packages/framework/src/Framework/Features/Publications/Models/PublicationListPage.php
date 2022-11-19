<?php

declare(strict_types=1);

namespace Hyde\Framework\Features\Publications\Models;

class PublicationListPage extends \Hyde\Pages\BladePage
{
    public static string $sourceDirectory = '%type%';
    public static string $outputDirectory = '%type%';
    public static string $fileExtension = '';
}
