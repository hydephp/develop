<?php

namespace Hyde\RealtimeCompiler\Routing;

use Desilva\Microserve\Request;
use Desilva\Microserve\Response;
use Hyde\Framework\Actions\StaticPageBuilder;
use Hyde\Framework\Features\Documentation\DocumentationSearchPage;
use Hyde\Pages\Concerns\HydePage;
use Hyde\RealtimeCompiler\Concerns\InteractsWithLaravel;
use Hyde\RealtimeCompiler\Concerns\SendsErrorResponses;
use Hyde\RealtimeCompiler\Http\DashboardController;
use Hyde\RealtimeCompiler\Http\HtmlResponse;
use Hyde\Support\Models\Route;

/**
 * Handle routing for a web page request.
 */
class PageRouter
{
    use SendsErrorResponses;
    use InteractsWithLaravel;

    protected Request $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
        $this->bootApplication();
    }

    protected function handlePageRequest(): Response
    {
        if ($this->request->path === '/dashboard' && DashboardController::enabled()) {
            return new HtmlResponse(200, 'OK', [
                'body' => (new DashboardController())->show(),
            ]);
        }

        $html = $this->getHtml($this->getPageFromRoute());

        return (new Response(200, 'OK', [
            'body' => $html,
        ]))->withHeaders([
            'Content-Type'   => 'text/html',
            'Content-Length' => strlen($html),
        ]);
    }

    protected function normalizePath(string $path): string
    {
        // If URL ends in .html, strip it
        if (str_ends_with($path, '.html')) {
            $path = substr($path, 0, -5);
        }

        // If the path is empty, serve the index file
        if (empty($path) || $path == '/') {
            $path = '/index';
        }

        return ltrim($path, '/');
    }

    protected function getHtml(HydePage $page): string
    {
        if ($page->identifier === 'index' && DashboardController::enabled()) {
            return DashboardController::renderIndexPage($page);
        }

        return file_get_contents((new StaticPageBuilder($page))->__invoke());
    }

    public static function handle(Request $request): Response
    {
        return (new self($request))->handlePageRequest();
    }

    protected function getPageFromRoute(): HydePage
    {
        if ($this->request->path === '/docs/search') {
            return (new DocumentationSearchPage());
        }

        return Route::getOrFail($this->normalizePath($this->request->path))->getPage();
    }
}
