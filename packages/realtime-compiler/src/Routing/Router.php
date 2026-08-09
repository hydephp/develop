<?php

declare(strict_types=1);

namespace Hyde\RealtimeCompiler\Routing;

use Desilva\Microserve\Request;
use Desilva\Microserve\Response;
use Hyde\RealtimeCompiler\RealtimeCompiler;
use Hyde\RealtimeCompiler\Actions\AssetFileLocator;
use Hyde\RealtimeCompiler\Concerns\SendsErrorResponses;
use Hyde\RealtimeCompiler\Models\FileObject;
use Hyde\RealtimeCompiler\Concerns\InteractsWithLaravel;

class Router
{
    use SendsErrorResponses;
    use InteractsWithLaravel;

    protected Request $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function handle(): Response
    {
        // Media files are always static assets, so we proxy them
        // directly without paying for booting the application.
        if (str_starts_with($this->request->path, '/media/')) {
            return $this->proxyStatic();
        }

        $this->bootApplication();

        $this->overrideSiteUrl();

        $virtualRoutes = app(RealtimeCompiler::class)->getVirtualRoutes();

        if (isset($virtualRoutes[$this->request->path])) {
            return $virtualRoutes[$this->request->path]($this->request);
        }

        // A path with a file extension that isn't a web page is a static asset request,
        // unless a page route is registered for the path (like `docs/search.json`),
        // as pages take precedence over the on-disk files the proxy serves.
        if ($this->hasAssetLikeExtension() && ! PageRouter::hasRoute($this->request)) {
            return $this->proxyStatic();
        }

        return PageRouter::handle($this->request);
    }

    /**
     * Does the request path have an extension that isn't a web page?
     *
     * Paths without an extension are pretty urls, and .html paths are compiled pages.
     */
    protected function hasAssetLikeExtension(): bool
    {
        $extension = pathinfo($this->request->path)['extension'] ?? null;

        return $extension !== null && $extension !== 'html';
    }

    /**
     * Override the configured site URL so compiled pages reference the local
     * server instead of the production URL. Without this, assets such as
     * media files would be loaded from the production host when previewing
     * the site locally.
     */
    protected function overrideSiteUrl(): void
    {
        // When save_preview is enabled, the compiled page is written to disk,
        // so we leave the configured site URL alone to avoid baking the local
        // preview URL into the persisted output.
        if (config('hyde.server.save_preview')) {
            return;
        }

        $scheme = (! empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';

        $host = $this->sanitizeHost($_SERVER['HTTP_HOST'] ?? null)
            ?? $this->getConfiguredServerHost($scheme);

        config(['hyde.url' => "$scheme://$host"]);
    }

    /**
     * Sanitize a host header value before using it in generated URLs.
     */
    protected function sanitizeHost(?string $host): ?string
    {
        if ($host === null) {
            return null;
        }

        $host = trim($host);

        if ($host === '') {
            return null;
        }

        // Reject whitespace, path separators, and other obvious host-header injection vectors.
        if (preg_match('/[\s\/\\\\@]/', $host)) {
            return null;
        }

        // Allow normal hostnames with optional ports, plus localhost.
        if (! preg_match('/^[A-Za-z0-9.-]+(?::[0-9]{1,5})?$/', $host)) {
            return null;
        }

        if (str_contains($host, ':')) {
            $port = (int) substr(strrchr($host, ':'), 1);

            if ($port < 1 || $port > 65535) {
                return null;
            }
        }

        return $host;
    }

    /**
     * Get the configured local server host, including the port when non-default.
     */
    protected function getConfiguredServerHost(string $scheme): string
    {
        $host = $this->sanitizeHost(config('hyde.server.host', 'localhost')) ?? 'localhost';
        $port = (int) config('hyde.server.port', 0);

        $isDefaultPort = ($scheme === 'https' && $port === 443)
            || ($scheme === 'http' && $port === 80);

        if ($port > 0 && $port <= 65535 && ! $isDefaultPort && ! str_contains($host, ':')) {
            return "$host:$port";
        }

        return $host;
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
            'Content-Type' => $file->getMimeType(),
            'Content-Length' => $file->getContentLength(),
        ]);
    }
}
