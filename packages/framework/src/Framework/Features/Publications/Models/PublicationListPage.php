<?php

declare(strict_types=1);

namespace Hyde\Framework\Features\Publications\Models;

use Hyde\Framework\Features\Publications\PublicationHelper;
use Hyde\Hyde;
use Hyde\Pages\BladePage;

use Illuminate\Support\Facades\Blade;

use function array_merge;
use function array_unique;
use function base_path;
use function config;
use function file_get_contents;
use function str_contains;
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
        $data = [
            'publications' => PublicationHelper::getPublicationsForPubType($this->type),
        ];

        $template = $this->type->getSchema()['listTemplate'];
        if (str_contains($template, '::')) {
            return view($template, $data)->render();
        }
        return Blade::render(
            file_get_contents(Hyde::path("{$this->type->getDirectory()}/$template").'.blade.php'), $data
        );
    }

    public function getSourcePath(): string
    {
        return $this->type->getDirectory().'/schema.json';
    }
}
