<?php

namespace Hyde\RealtimeCompiler\Routing;

use Desilva\Microserve\Request;
use Desilva\Microserve\Response;
use Hyde\Framework\Hyde;
use Hyde\Framework\Models\Pages\BladePage;
use Hyde\Framework\Models\Pages\DocumentationPage;
use Hyde\Framework\Models\Pages\MarkdownPage;
use Hyde\Framework\Models\Pages\MarkdownPost;
use Hyde\RealtimeCompiler\Actions\Compiler;
use Hyde\RealtimeCompiler\Concerns\InteractsWithLaravel;
use Hyde\RealtimeCompiler\Concerns\SendsErrorResponses;

/**
 * Handle routing for a web page request.
 *
 * Does not send 404 responses upon missing source files,
 * instead letting an exception be thrown, as it is
 * better handled by the ExceptionHandler.
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
        $requestPath = $this->normalizePath($this->request->path);
        $sourceFilePath = $this->decodeSourceFilePath($requestPath);
        $sourceFileModel = $this->decodeSourceFileModel($sourceFilePath);

        $html = $this->getHtml($sourceFileModel, $sourceFilePath);

        return (new Response(200, 'OK', [
            'body' => $html,
        ]))->withHeaders([
            'Content-Type'   => 'text/html',
            'Content-Length' => strlen($html),
        ]);
    }

    protected function normalizePath(string $path): string
    {
        // If uri ends in .html, strip it
        if (str_ends_with($path, '.html')) {
            $path = substr($path, 0, -5);
        }

        // If the path is empty, serve the index file
        if (empty($path) || $path == '/') {
            $path = '/index';
        }

        return $path;
    }

    protected function decodeSourceFilePath(string $path): string
    {
        // Todo get paths from model class instead of hardcoded
        if (str_starts_with($path, '/posts/')) {
            return '_posts/'.basename($path);
        }

        if (str_starts_with($path, '/docs/')) {
            return '_docs/'.basename($path);
        }

        return '_pages/'.basename($path);
    }

    protected function decodeSourceFileModel(string $path): string
    {
        if (str_starts_with($path, '_posts/')) {
            return MarkdownPost::class;
        }

        if (str_starts_with($path, '_docs/')) {
            return DocumentationPage::class;
        }

        if (file_exists(Hyde::path($path.'.md'))) {
            return MarkdownPage::class;
        }

        return BladePage::class;
    }

    protected function getHtml(string $model, string $path): string
    {
        // todo add caching as we don't need to recompile pages that have not changed
        return (new Compiler($model, $path))->render();
    }

    public static function handle(Request $request): Response
    {
        return (new self($request))->handlePageRequest();
    }
}
