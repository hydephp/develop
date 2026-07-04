<?php

declare(strict_types=1);

namespace Hyde\RealtimeCompiler\Routing;

use Desilva\Microserve\Request;
use Desilva\Microserve\Response;
use Hyde\Facades\Features;
use Hyde\RealtimeCompiler\RealtimeCompiler;
use Hyde\RealtimeCompiler\Actions\AssetFileLocator;
use Hyde\RealtimeCompiler\Concerns\SendsErrorResponses;
use Hyde\RealtimeCompiler\Http\VirtualRouteController;
use Hyde\RealtimeCompiler\Models\FileObject;
use Hyde\RealtimeCompiler\Concerns\InteractsWithLaravel;
use Hyde\Framework\Features\XmlGenerators\RssFeedGenerator;

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
        if ($this->shouldProxy($this->request)) {
            return $this->proxyStatic();
        }

        $this->bootApplication();

        $this->overrideSiteUrl();

        $this->registerDynamicVirtualRoutes();

        $virtualRoutes = app(RealtimeCompiler::class)->getVirtualRoutes();

        if (isset($virtualRoutes[$this->request->path])) {
            return $virtualRoutes[$this->request->path]($this->request);
        }

        return PageRouter::handle($this->request);
    }

    /**
     * Register virtual routes whose availability depends on the site URL, which is only
     * finalized after {@see overrideSiteUrl()} has run. Unlike the routes registered in
     * the service provider's boot method, these can't be resolved any earlier: outside of
     * `save_preview` mode, the site URL is always overridden to a local address, so (unlike
     * a real `hyde build`) we don't need a production site URL to be configured to serve
     * these on the local dev server.
     */
    protected function registerDynamicVirtualRoutes(): void
    {
        $compiler = app(RealtimeCompiler::class);

        if (Features::hasSitemap()) {
            $compiler->registerVirtualRoute('/sitemap.xml', [VirtualRouteController::class, 'sitemap']);
        }

        if (Features::hasRss()) {
            $compiler->registerVirtualRoute('/'.RssFeedGenerator::getFilename(), [VirtualRouteController::class, 'rssFeed']);
        }
    }

    /**
     * If the request is not for a web page, we assume it's
     * a static asset, which we instead want to proxy.
     */
    protected function shouldProxy(Request $request): bool
    {
        // Always proxy media files. This condition is just to improve performance
        // without having to check the file extension.
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

        // Don't proxy the search.json file, as it's generated on the fly.
        if (str_ends_with($request->path, 'search.json')) {
            return false;
        }

        // Don't proxy the sitemap, as it's generated on the fly.
        // Note that unlike the RSS feed below, the sitemap filename is not configurable.
        if ($request->path === '/sitemap.xml') {
            return false;
        }

        // Don't proxy the RSS feed, as it's generated on the fly.
        // We can't resolve the configured `hyde.rss.filename` here as the application
        // is not booted yet, so we match against the default filename instead.
        if ($request->path === '/feed.xml') {
            return false;
        }

        // The page is not a web page, so we assume it should be proxied.
        return true;
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
