<?php

declare(strict_types=1);

namespace Hyde\Framework\Features\Publications\Models;

use Hyde\Framework\Features\Publications\PublicationHelper;
use Hyde\Pages\BladePage;

use Illuminate\Support\Str;

use function view;

class PublicationListPage extends BladePage
{
    public static string $sourceDirectory = '__publications';
    public static string $outputDirectory = '';
    public static string $fileExtension = 'json';

    public PublicationType $type;

    public function __construct(PublicationType $type)
    {
        parent::__construct("{$type->getDirectory()}/index");
        $this->type = $type;
    }

    public function compile(): string
    {
        $listTemplate = $this->type->getSchema()['listTemplate'];
        $listTemplate = Str::before("$listTemplate", '.blade.php');

        $pubType  = $this->type;
        $template = 'pubtypes.'.$listTemplate;
        $publications = PublicationHelper::getPublicationsForPubType($pubType);
        return view($template)->with('publications', $publications)->render();
    }
}
