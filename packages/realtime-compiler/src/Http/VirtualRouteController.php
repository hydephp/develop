<?php

declare(strict_types=1);

namespace Hyde\RealtimeCompiler\Http;

use Hyde\Facades\Features;
use Desilva\Microserve\Request;
use Desilva\Microserve\Response;
use Desilva\Microserve\JsonResponse;
use Hyde\Framework\Features\XmlGenerators\RssFeedGenerator;
use Hyde\Framework\Features\XmlGenerators\SitemapGenerator;

use function strlen;

class VirtualRouteController
{
    public static function ping(): JsonResponse
    {
        return new JsonResponse(200, 'OK', [
            'server' => 'Hyde/RealtimeCompiler',
        ]);
    }

    public static function dashboard(Request $request): Response
    {
        return (new DashboardController($request))->handle();
    }

    public static function liveEdit(Request $request): Response
    {
        return (new LiveEditController($request))->handle();
    }

    public static function openInEditor(Request $request): Response
    {
        return (new OpenInEditorController($request))->handle();
    }

    public static function sitemap(): Response
    {
        if (! Features::hasSitemap()) {
            return new Response(404, 'Not Found');
        }

        return static::xmlResponse(SitemapGenerator::make(), 'application/xml');
    }

    public static function rssFeed(): Response
    {
        if (! Features::hasRss()) {
            return new Response(404, 'Not Found');
        }

        return static::xmlResponse(RssFeedGenerator::make(), 'application/rss+xml');
    }

    protected static function xmlResponse(string $xml, string $contentType): Response
    {
        return (new Response(200, 'OK', [
            'body' => $xml,
        ]))->withHeaders([
            'Content-Type' => $contentType,
            'Content-Length' => (string) strlen($xml),
        ]);
    }
}
