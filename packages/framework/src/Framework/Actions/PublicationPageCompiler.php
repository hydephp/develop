<?php

declare(strict_types=1);

namespace Hyde\Framework\Actions;

use Hyde\Framework\Concerns\InvokableAction;
use Hyde\Framework\Features\Publications\Models\PublicationListPage;
use Hyde\Framework\Features\Publications\Models\PublicationType;
use Hyde\Framework\Features\Publications\PublicationService;
use Hyde\Hyde;
use Hyde\Pages\HtmlPage;
use Hyde\Pages\PublicationPage;
use Illuminate\Support\Facades\View;

use function array_merge;
use function str_ends_with;

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
        // FIXME implement condition
        if (true) {
            $this->generatePaginationPages();
            return '';
        }

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
        return str_ends_with($template, '.blade.php')
            ? AnonymousViewCompiler::call($this->getTemplateFilePath($template), $data)
            : View::make($template, $data)->render();
    }

    protected function getTemplateFilePath(string $template): string
    {
        $template = basename($template, '.blade.php');

        return "{$this->page->type->getDirectory()}/$template.blade.php";
    }

    protected function generatePaginationPages(): void
    {
        $pubType = $this->page->type;
        $pages = PublicationService::getPublicationsForPubType($pubType);

        $count = 25; //FIXme get form type
        $chunks = $pages->chunk($count);

        foreach ($chunks as $index => $chunk) {
            $page = $index + 1;
            $data = [
                'publications' => $chunk,
                'pagination' => [
                    'current' => $page,
                    'total' => $chunks->count(),
                    'next' => $page < $chunks->count() ? $page + 1 : null,
                    'previous' => $page > 1 ? $page - 1 : null,
                    'offset' => $index * $count + 1,
                ],
            ];

            $this->savePaginationPage($pubType, $page, $data);
        }
    }

    protected function savePaginationPage(PublicationType $pubType, int $pageNumber, array $data) {
        $identifier = "{$pubType->getDirectory()}/page-$pageNumber";
        $page = new HtmlPage($identifier, matter: [
            'title' => "Publications for type ".$pubType->name . " (Page - $pageNumber)",
        ]); // virtual page?
        Hyde::shareViewData($page);
        $path = "$identifier.html";
        $content = view('hyde::layouts.publication_paginated_list', array_merge([
            'type' => $pubType,
            'paginator' => (object) $data['pagination'],
        ], $data));

        file_put_contents(Hyde::sitePath($path), $content);
    }
}
