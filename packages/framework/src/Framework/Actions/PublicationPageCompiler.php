<?php

declare(strict_types=1);

namespace Hyde\Framework\Actions;

use Hyde\Framework\Concerns\InvokableAction;
use Hyde\Framework\Features\Publications\Models\PublicationListPage;
use Hyde\Framework\Features\Publications\PublicationService;
use Hyde\Pages\PublicationPage;
use Illuminate\Support\Facades\Blade;
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
        return $this->compile($template, $data);
    }

    public function compilePublicationListPage(): string
    {
        $data = [
            'publications' => PublicationService::getPublicationsForPubType($this->page->type),
        ];

        $template = $this->page->type->listTemplate;
        return $this->compile($template, $data);
    }

    protected function getTemplateFilePath(string $template): string
    {
        return "{$this->page->type->getDirectory()}/$template.blade.php";
    }

    protected function compile(string $template, array $data): string
    {
        if (view()->exists($template)) {
            return view($template, $data)->render();
        }

        return AnonymousViewCompiler::call($this->getTemplateFilePath($template), $data);
    }
}
