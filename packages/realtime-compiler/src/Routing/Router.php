<?php

namespace Hyde\RealtimeCompiler\Routing;

use Desilva\Microserve\JsonResponse;
use Desilva\Microserve\Request;
use Desilva\Microserve\Response;
use Hyde\RealtimeCompiler\Actions\AssetFileLocator;
use Hyde\RealtimeCompiler\Actions\RendersSearchPage;
use Hyde\RealtimeCompiler\Concerns\SendsErrorResponses;
use Hyde\RealtimeCompiler\Http\HtmlResponse;
use Hyde\RealtimeCompiler\Models\FileObject;

class Router
{
    use SendsErrorResponses;

    protected Request $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function handle(): Response
    {
        if ($this->shouldProxy($this->request)) {
            return $this->proxyStatic();
        }

        if ($this->shouldRenderSpecial($this->request)) {
            if ($this->request->path === '/docs') {
                $this->request->path = '/docs/index';
            }

            if ($this->request->path === '/docs/search') {
                return new HtmlResponse(200, 'OK', [
                    'body' => (new RendersSearchPage())->__invoke(),
                ]);
            }

            if ($this->request->path === '/ping') {
                return new JsonResponse(200, 'OK', [
                    'server' => 'Hyde/RealtimeCompiler',
                ]);
            }
        }

        return PageRouter::handle($this->request);
    }

    /**
     * If the request is not for a web page, we assume it's
     * a static asset, which we instead want to proxy.
     */
    protected function shouldProxy(Request $request): bool
    {
        // Always proxy media files
        if (str_starts_with($request->path, '/media/')) {
            return true;
        }

        // Get the requested file extension
        $extension = pathinfo($request->path)['extension'] ?? null;

        // If the extension is not set (pretty url), or is .html,
        // we assume it's a web page which we need to compile.
        if ($extension === null || $extension === 'html') {
            return false;
        }

        // The page is not a web page, so we assume it should be proxied.
        return true;
    }

    /**
     * Proxy a static file or return a 404.
     */
    protected function proxyStatic(): Response
    {
        $path = AssetFileLocator::find($this->request->path);

        if ($path === null) {
            return $this->notFound();
        }

        $file = new FileObject($path);

        return (new Response(200, 'OK', [
            'body' => $file->getStream(),
        ]))->withHeaders([
            'Content-Type'   => $file->getMimeType(),
            'Content-Length' => $file->getContentLength(),
        ]);
    }

    /**
     * If the request is for a special page, we handle it here.
     */
    protected function shouldRenderSpecial(Request $request): bool
    {
        $routes = [
            '/ping',
            '/docs',
            '/docs/search',
        ];

        return in_array($request->path, $routes);
    }
}
