<?php

declare(strict_types=1);

namespace Hyde\Framework\Actions;

use Hyde\Facades\Filesystem;
use Hyde\Framework\Concerns\InvokableAction;
use Hyde\Framework\Features\Publications\Models\PublicationListPage;
use Hyde\Framework\Features\Publications\PublicationService;
use Hyde\Pages\PublicationPage;
use Illuminate\Support\Facades\View;

/**
 * @todo Consider changing to check if template key ends with .blade.php and using that to signify if it's an anonymous view.
 *
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

    protected function compilePublicationPage(): string
    {
        return $this->compileView($this->page->type->detailTemplate, [
            'publication' => $this->page,
        ]);
    }

    protected function compilePublicationListPage(): string
    {
        return $this->compileView($this->page->type->listTemplate, [
            'publications' => PublicationService::getPublicationsForPubType($this->page->type),
        ]);
    }

    protected function compileView(string $template, array $data): string
    {
        return Filesystem::exists($this->getTemplateFilePath($template))
            ? AnonymousViewCompiler::call($this->getTemplateFilePath($template), $data)
            : View::make($template, $data)->render();
    }

    protected function getTemplateFilePath(string $template): string
    {
        $template = basename($template, '.blade.php');
        return "{$this->page->type->getDirectory()}/$template.blade.php";
    }
}
