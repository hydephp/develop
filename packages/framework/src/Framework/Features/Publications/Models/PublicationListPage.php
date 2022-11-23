<?php

declare(strict_types=1);

namespace Hyde\Framework\Features\Publications\Models;

use function file_get_contents;
use Hyde\Framework\Features\Publications\PublicationService;
use Hyde\Hyde;
use Hyde\Pages\BladePage;
use Illuminate\Support\Facades\Blade;
use InvalidArgumentException;
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
            'publications' => PublicationService::getPublicationsForPubType($this->type),
        ];

        $template = $this->type->listTemplate;
        if (str_contains($template, '::')) {
            return view($template, $data)->render();
        }

        // Using the Blade facade we can render any file without having to register the directory with the view finder.
        $viewPath = Hyde::path("{$this->type->getDirectory()}/$template").'.blade.php';
        if (! file_exists($viewPath)) {
            throw new InvalidArgumentException("View [$viewPath] not found.");
        }

        return Blade::render(
            file_get_contents($viewPath), $data
        );
    }

    public function getSourcePath(): string
    {
        return $this->type->getDirectory().'/schema.json';
    }
}
