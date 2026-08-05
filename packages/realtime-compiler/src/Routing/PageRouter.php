<?php

declare(strict_types=1);

namespace Hyde\RealtimeCompiler\Routing;

use Desilva\Microserve\Request;
use Desilva\Microserve\Response;
use Hyde\Facades\Localization;
use Hyde\Foundation\Facades\Routes;
use Hyde\Support\Models\Route;
use Hyde\Pages\Concerns\BaseMarkdownPage;
use Hyde\Framework\Actions\StaticPageBuilder;
use Hyde\RealtimeCompiler\Http\LiveEditController;
use Hyde\Framework\Exceptions\RouteNotFoundException;
use Hyde\Pages\Concerns\HydePage;
use Hyde\RealtimeCompiler\Concerns\SendsErrorResponses;
use Hyde\RealtimeCompiler\Http\DashboardController;
use Desilva\Microserve\HtmlResponse;
use Hyde\Hyde;

/**
 * Handle routing for a web page request.
 */
class PageRouter
{
    use SendsErrorResponses;

    protected Request $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    protected function handlePageRequest(): Response
    {
        $page = $this->getPageFromRoute();
        $body = $this->getHtml($page);

        $contentType = $this->getContentType($page);

        if ($contentType !== 'text/html') {
            return (new Response(200, 'OK', [
                'body' => $body,
            ]))->withHeaders([
                'Content-Type' => $contentType,
                'Content-Length' => (string) strlen($body),
            ]);
        }

        return new HtmlResponse(200, 'OK', [
            'body' => $body,
        ]);
    }

    /**
     * Determine the response content type based on the compiled page's output file extension.
     */
    protected function getContentType(HydePage $page): string
    {
        $extension = pathinfo($page->getOutputPath(), PATHINFO_EXTENSION);

        return match ($extension) {
            'json' => 'application/json',
            'xml' => 'application/xml',
            'txt' => 'text/plain',
            default => 'text/html',
        };
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

        if (config('hyde.server.save_preview')) {
            $contents = file_get_contents(StaticPageBuilder::handle($page));
        } else {
            Hyde::shareViewData($page);

            $contents = Localization::usingLanguage($page->getLanguage(), fn (): string => $page->compile());
        }

        if ($page instanceof BaseMarkdownPage && LiveEditController::enabled()) {
            $contents = LiveEditController::injectLiveEditScript($contents);
        }

        return $contents;
    }

    public static function handle(Request $request): Response
    {
        return (new self($request))->handlePageRequest();
    }

    /**
     * Does the request match a page route? This allows the router to tell a missing
     * page apart from a missing static asset, without having to compile the page.
     */
    public static function hasRoute(Request $request): bool
    {
        return (new self($request))->findRoute() !== null;
    }

    protected function getPageFromRoute(): HydePage
    {
        $route = $this->findRoute() ?? throw new RouteNotFoundException($this->normalizePath($this->request->path));

        return $route->getPage();
    }

    protected function findRoute(): ?Route
    {
        $routeKey = $this->normalizePath($this->request->path);

        // Directory-style requests (like `/docs/1.x`) are served by their index page.
        return Routes::find($routeKey) ?? Routes::find("$routeKey/index");
    }
}
