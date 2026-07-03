<?php

declare(strict_types=1);

namespace Hyde\RealtimeCompiler\Http;

use Desilva\Microserve\Request;
use Desilva\Microserve\Response;
use Desilva\Microserve\JsonResponse;
use Hyde\Framework\Features\XmlGenerators\SitemapGenerator;
use Hyde\Framework\Features\XmlGenerators\RssFeedGenerator;

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
        return (new Response(200, 'OK', [
            'body' => SitemapGenerator::make(),
        ]))->withHeaders([
            'Content-Type' => 'application/xml',
        ]);
    }

    public static function rssFeed(): Response
    {
        return (new Response(200, 'OK', [
            'body' => RssFeedGenerator::make(),
        ]))->withHeaders([
            'Content-Type' => 'application/rss+xml',
        ]);
    }
}
