<?php

declare(strict_types=1);

namespace Hyde\Framework\Actions;

use function file_exists;
use function file_get_contents;
use Hyde\Framework\Concerns\InvokableAction;
use Hyde\Framework\Features\Publications\Models\PublicationListPage;
use Hyde\Framework\Features\Publications\PublicationService;
use Hyde\Hyde;
use Hyde\Pages\PublicationPage;
use Illuminate\Support\Facades\Blade;
use InvalidArgumentException;
use function view;

/**
 * @see \Hyde\Framework\Testing\Feature\Actions\PublicationPageCompilerTest
 */
class PublicationPageCompiler extends InvokableAction
{
    protected PublicationPage|PublicationListPage $page;

    public function __construct(PublicationPage|PublicationListPage $page)
    {
        $this->page = $page;
    }

    public function __invoke(): string
    {
        return $this->page instanceof PublicationPage
            ? $this->compilePublicationPage()
            : $this->compilePublicationListPage();
    }

    public function compilePublicationPage(): string
    {
        $data = [
            'publication' => $this->page,
        ];

        $template = $this->page->type->detailTemplate;
        if (str_contains($template, '::')) {
            return view($template, $data)->render();
        }

        // Using the Blade facade we can render any file without having to register the directory with the view finder.
        return AnonymousViewCompiler::call("{$this->page->type->getDirectory()}/$template.blade.php", $data);
    }

    public function compilePublicationListPage(): string
    {
        $data = [
            'publications' => PublicationService::getPublicationsForPubType($this->page->type),
        ];

        $template = $this->page->type->listTemplate;
        if (str_contains($template, '::')) {
            return view($template, $data)->render();
        }

        // Using the Blade facade we can render any file without having to register the directory with the view finder.
        return AnonymousViewCompiler::call("{$this->page->type->getDirectory()}/$template" . '.blade.php', $data);
    }
}
