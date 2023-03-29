<?php

declare(strict_types=1);

namespace Hyde\Publications\Actions;

use Illuminate\Support\Facades\View;
use Hyde\Publications\Models\PublicationPage;
use Hyde\Framework\Actions\AnonymousViewCompiler;
use Hyde\Publications\Models\PublicationListPage;

use function basename;
use function str_ends_with;

/**
 * @see \Hyde\Publications\Testing\Feature\PublicationPageCompilerTest
 */
class PublicationPageCompiler
{
    protected PublicationPage|PublicationListPage $page;

    public static function call(PublicationPage|PublicationListPage $page): string
    {
        return (new self($page))->__invoke();
    }

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
            'publicationType' => $this->page->type,
        ]);
    }

    protected function compileView(string $template, array $data): string
    {
        return str_ends_with($template, '.blade.php')
            ? AnonymousViewCompiler::handle($this->getTemplateFilePath($template), $data)
            : View::make($template, $data)->render();
    }

    protected function getTemplateFilePath(string $template): string
    {
        $template = basename($template, '.blade.php');

        return "{$this->page->type->getDirectory()}/$template.blade.php";
    }
}
