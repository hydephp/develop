<?php

declare(strict_types=1);

use Hyde\Testing\TestCase;
use Desilva\Microserve\JsonResponse;
use Desilva\Microserve\Request;
use Desilva\Microserve\Response;
use Hyde\Facades\Filesystem;
use Hyde\Pages\InMemoryPage;
use Hyde\Framework\Exceptions\RouteNotFoundException;
use Hyde\RealtimeCompiler\Http\ExceptionHandler;
use Desilva\Microserve\HtmlResponse;
use Hyde\RealtimeCompiler\Http\HttpKernel;
use Hyde\RealtimeCompiler\Routing\PageRouter;
use Hyde\RealtimeCompiler\Routing\Router;

class RealtimeCompilerTest extends TestCase
{
    protected array $serverBackup = [];

    public static function setUpBeforeClass(): void
    {
        putenv('SERVER_LIVE_EDIT=false');

        define('BASE_PATH', realpath(__DIR__.'/../../../'));

        if (BASE_PATH === false || ! file_exists(BASE_PATH.'/hyde')) {
            throw new InvalidArgumentException('This test suite must be run from the root of the hydephp/develop monorepo.');
        }
    }

    protected function setUp(): void
    {
        parent::setUp();
        ob_start();

        $this->serverBackup = $_SERVER;
    }

    protected function tearDown(): void
    {
        $_SERVER = $this->serverBackup;

        parent::tearDown();
        ob_end_clean();
    }

    public function testHandlesRoutesIndexPage()
    {
        putenv('SERVER_DASHBOARD=false');
        $this->mockCompilerRoute('');

        $kernel = new HttpKernel();
        $response = $kernel->handle(new Request());

        $this->assertInstanceOf(Response::class, $response);
        $this->assertSame(200, $response->statusCode);
        $this->assertSame('OK', $response->statusMessage);
        $this->assertStringContainsString('<title>Welcome to HydePHP!</title>', $response->body);
    }

    public function testHandlesRoutesCustomPages()
    {
        $this->mockCompilerRoute('foo');

        Filesystem::put('_pages/foo.md', '# Hello World!');

        $kernel = new HttpKernel();
        $response = $kernel->handle(new Request());

        $this->assertInstanceOf(Response::class, $response);
        $this->assertSame(200, $response->statusCode);
        $this->assertSame('OK', $response->statusMessage);
        $this->assertStringContainsString('<h1>Hello World!</h1>', $response->body);

        Filesystem::unlink('_pages/foo.md');
    }

    public function testHandlesRoutesPagesWithHtmlExtension()
    {
        $this->mockCompilerRoute('foo.html');
        Filesystem::put('_pages/foo.md', '# Hello World!');

        $kernel = new HttpKernel();
        $response = $kernel->handle(new Request());

        $this->assertInstanceOf(Response::class, $response);
        $this->assertSame(200, $response->statusCode);
        $this->assertSame('OK', $response->statusMessage);
        $this->assertStringContainsString('<h1>Hello World!</h1>', $response->body);

        Filesystem::unlink('_pages/foo.md');
    }

    public function testHandlesRoutesStaticAssets()
    {
        $this->mockCompilerRoute('media/test.css');
        Filesystem::put('_media/test.css', 'test');

        $kernel = new HttpKernel();
        $response = $kernel->handle(new Request());

        $this->assertInstanceOf(Response::class, $response);
        $this->assertSame(200, $response->statusCode);
        $this->assertSame('OK', $response->statusMessage);
        $this->assertSame('test', $response->body);

        Filesystem::unlink('_media/test.css');
    }

    public function testNormalizesMediaPath()
    {
        $this->mockCompilerRoute('media/test.css');
        Filesystem::put('_media/test.css', 'test');

        $kernel = new HttpKernel();
        $response = $kernel->handle(new Request());

        $this->assertInstanceOf(Response::class, $response);
        $this->assertSame(200, $response->statusCode);
        $this->assertSame('OK', $response->statusMessage);
        $this->assertSame('test', $response->body);

        Filesystem::unlink('_media/test.css');
    }

    public function testThrowsRouteNotFoundExceptionForMissingRoute()
    {
        $this->mockCompilerRoute('missing');

        $kernel = new HttpKernel();

        $this->expectException(RouteNotFoundException::class);
        $this->expectExceptionMessage('Route [missing] not found');

        $kernel->handle(new Request());
    }

    public function testSends404ErrorResponseForMissingAsset()
    {
        $this->mockCompilerRoute('missing.css');

        $kernel = new HttpKernel();
        $response = $kernel->handle(new Request());

        $this->assertInstanceOf(Response::class, $response);
        $this->assertSame(404, $response->statusCode);
        $this->assertSame('Not Found', $response->statusMessage);
    }

    public function testTrailingSlashesAreNormalizedFromRoute()
    {
        $this->mockCompilerRoute('foo/');

        Filesystem::put('_pages/foo.md', '# Hello World!');

        $kernel = new HttpKernel();
        $response = $kernel->handle(new Request());

        $this->assertInstanceOf(Response::class, $response);
        $this->assertSame(200, $response->statusCode);
        $this->assertSame('OK', $response->statusMessage);
        $this->assertStringContainsString('<h1>Hello World!</h1>', $response->body);

        Filesystem::unlink('_pages/foo.md');
    }

    public function testDocsUriPathIsReroutedToDocsIndex()
    {
        $this->mockCompilerRoute('docs');

        Filesystem::put('_docs/index.md', '# Hello World!');

        $kernel = new HttpKernel();
        $response = $kernel->handle(new Request());

        $this->assertInstanceOf(Response::class, $response);
        $this->assertSame(200, $response->statusCode);
        $this->assertSame('OK', $response->statusMessage);
        $this->assertStringContainsString('HydePHP Docs', $response->body);

        Filesystem::unlink('_docs/index.md');
    }

    public function testDocsSearchRendersSearchPage()
    {
        $this->mockCompilerRoute('docs/search');
        Filesystem::put('_docs/index.md', '# Hello World!');

        $kernel = new HttpKernel();
        $response = $kernel->handle(new Request());

        $this->assertInstanceOf(HtmlResponse::class, $response);
        $this->assertSame(200, $response->statusCode);
        $this->assertSame('OK', $response->statusMessage);
        $this->assertStringContainsString('Search the documentation site', $response->body);

        Filesystem::unlink('_docs/index.md');
    }

    public function testDocsSearchJsonRendersSearchIndexWithJsonContentType()
    {
        $this->mockCompilerRoute('docs/search.json');
        Filesystem::put('_docs/index.md', '# Hello World!');

        $kernel = new HttpKernel();
        $response = $kernel->handle(new Request());

        $this->assertInstanceOf(Response::class, $response);
        $this->assertNotInstanceOf(HtmlResponse::class, $response);
        $this->assertSame(200, $response->statusCode);
        $this->assertSame('OK', $response->statusMessage);

        $headers = $this->getResponseHeaders($response);
        $this->assertSame('application/json', $headers['Content-Type']);
        $this->assertSame((string) strlen($response->body), $headers['Content-Length']);

        $this->assertIsArray(json_decode($response->body, true));

        Filesystem::unlink('_docs/index.md');
    }

    public function testGetContentTypeReturnsApplicationJsonForJsonOutputPath()
    {
        $page = $this->makePageWithOutputPath('foo.json');

        $this->assertSame('application/json', $this->invokeGetContentType($page));
    }

    public function testGetContentTypeReturnsApplicationXmlForXmlOutputPath()
    {
        $page = $this->makePageWithOutputPath('foo.xml');

        $this->assertSame('application/xml', $this->invokeGetContentType($page));
    }

    public function testGetContentTypeReturnsTextPlainForTxtOutputPath()
    {
        $page = $this->makePageWithOutputPath('foo.txt');

        $this->assertSame('text/plain', $this->invokeGetContentType($page));
    }

    public function testGetContentTypeReturnsTextHtmlForHtmlOutputPath()
    {
        $page = $this->makePageWithOutputPath('foo.html');

        $this->assertSame('text/html', $this->invokeGetContentType($page));
    }

    public function testGetContentTypeDefaultsToTextHtmlForUnknownExtension()
    {
        $page = $this->makePageWithOutputPath('foo');

        $this->assertSame('text/html', $this->invokeGetContentType($page));
    }

    public function testSitemapRouteReturnsSitemapResponse()
    {
        // Note this works even without a production site URL configured: the router always
        // overrides the site URL to the local server address (unless save_preview is enabled),
        // so the sitemap and RSS feed are available on the dev server regardless of whether a
        // production URL has been set.
        $this->mockCompilerRoute('sitemap.xml');

        $kernel = new HttpKernel();
        $response = $kernel->handle(new Request());

        $this->assertInstanceOf(Response::class, $response);
        $this->assertSame(200, $response->statusCode);
        $this->assertSame('OK', $response->statusMessage);
        $this->assertSame('application/xml; charset=UTF-8', $this->getResponseHeaders($response)['Content-Type']);
        $this->assertStringContainsString('<?xml version="1.0" encoding="UTF-8"?>', $response->body);
        $this->assertStringContainsString('<urlset', $response->body);
    }

    public function testRssFeedRouteReturnsRssResponse()
    {
        $this->mockCompilerRoute('feed.xml');
        Filesystem::put('_posts/test-post.md', "---\ntitle: Test Post\ndescription: Test post description\n---\n\n# Test Post");

        $kernel = new HttpKernel();
        $response = $kernel->handle(new Request());

        $this->assertInstanceOf(Response::class, $response);
        $this->assertSame(200, $response->statusCode);
        $this->assertSame('OK', $response->statusMessage);
        $this->assertSame('application/rss+xml; charset=UTF-8', $this->getResponseHeaders($response)['Content-Type']);
        $this->assertStringContainsString('<?xml version="1.0" encoding="UTF-8"?>', $response->body);
        $this->assertStringContainsString('<rss ', $response->body);
        $this->assertStringContainsString('version="2.0"', $response->body);
        $this->assertStringContainsString('Test Post', $response->body);

        Filesystem::unlink('_posts/test-post.md');
    }

    public function testSitemapRouteIsNotRegisteredWhenSitemapGenerationIsDisabled()
    {
        // The Router creates a fresh application instance for every request (see
        // InteractsWithLaravel::createApplication()), so config values must be
        // changed on disk rather than through the config() helper for this to
        // take effect for the request handled below.
        $pattern = '/(\'generate_sitemap\'\s*=>\s*)true/';

        $this->withModifiedConfigFile($pattern, '${1}false', function () {
            $this->mockCompilerRoute('sitemap.xml');

            $kernel = new HttpKernel();

            $this->expectException(RouteNotFoundException::class);
            $this->expectExceptionMessage('Route [sitemap.xml] not found.');

            $kernel->handle(new Request());
        });
    }

    public function testRssFeedRouteIsNotRegisteredWhenRssGenerationIsDisabled()
    {
        // Scoped to the `rss` config array (rather than matching `'enabled' => true`
        // anywhere in the file) since other features may also have an `enabled` key.
        $pattern = '/(\'rss\'\s*=>\s*\[.*?\'enabled\'\s*=>\s*)true/s';

        $this->withModifiedConfigFile($pattern, '${1}false', function () {
            Filesystem::put('_posts/disabled-rss-test.md', "---\ntitle: Disabled RSS Test\n---\n\n# Disabled RSS Test");

            try {
                $this->mockCompilerRoute('feed.xml');

                $kernel = new HttpKernel();

                $this->expectException(RouteNotFoundException::class);
                $this->expectExceptionMessage('Route [feed.xml] not found.');

                $kernel->handle(new Request());
            } finally {
                Filesystem::unlink('_posts/disabled-rss-test.md');
            }
        });
    }

    public function testRssFeedRouteRespectsConfiguredCustomFilename()
    {
        // This proves the bug fix for shouldProxy() resolving the configured RSS filename:
        // before the fix, requesting the real (custom) feed path would incorrectly be
        // treated as a static asset request and result in a 404, since shouldProxy()
        // only ever matched against the hardcoded default filename `feed.xml`.
        $pattern = '/(\'rss\'\s*=>\s*\[.*?\'filename\'\s*=>\s*\')feed\.xml(\')/s';

        $this->withModifiedConfigFile($pattern, '${1}custom-feed.xml${2}', function () {
            Filesystem::put('_posts/custom-filename-test.md', "---\ntitle: Custom Filename Test\ndescription: Custom filename test description\n---\n\n# Custom Filename Test");

            try {
                $this->mockCompilerRoute('custom-feed.xml');

                $kernel = new HttpKernel();
                $response = $kernel->handle(new Request());

                $this->assertInstanceOf(Response::class, $response);
                $this->assertSame(200, $response->statusCode);
                $this->assertStringContainsString('<rss ', $response->body);
                $this->assertStringContainsString('Custom Filename Test', $response->body);
            } finally {
                Filesystem::unlink('_posts/custom-filename-test.md');
            }
        });
    }

    /**
     * Temporarily rewrite the project's `config/hyde.php` file by applying a regex
     * replacement, run the given callback, then always restore the original contents.
     *
     * This is needed because the Router creates a fresh application instance for
     * every request, so config values must be changed on disk (rather than through
     * the `config()` helper) to take effect for a request handled within $callback.
     */
    protected function withModifiedConfigFile(string $pattern, string $replacement, callable $callback): void
    {
        $configPath = BASE_PATH.'/config/hyde.php';
        $original = file_get_contents($configPath);

        $this->assertSame(1, preg_match($pattern, $original), 'Expected exactly one match for the config replacement pattern.');

        $modified = preg_replace($pattern, $replacement, $original, 1);

        file_put_contents($configPath, $modified);

        try {
            $callback();
        } finally {
            file_put_contents($configPath, $original);
        }
    }

    public function testPingRouteReturnsPingResponse()
    {
        $this->mockCompilerRoute('ping');

        $kernel = new HttpKernel();
        $response = $kernel->handle(new Request());

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertSame(200, $response->statusCode);
        $this->assertSame('OK', $response->statusMessage);
    }

    public function testExceptionHandling()
    {
        $exception = new Exception('foo');
        $response = ExceptionHandler::handle($exception);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertSame(500, $response->statusCode);
        $this->assertSame('Internal Server Error', $response->statusMessage);
    }

    public function testOverridesSiteUrlWithRequestUrl()
    {
        $this->mockCompilerRoute('');
        $_SERVER['HTTP_HOST'] = 'localhost:8080';

        config(['hyde.url' => 'https://hydephp.com']);

        $this->invokeOverrideSiteUrl();

        $this->assertSame('http://localhost:8080', config('hyde.url'));
    }

    public function testOverridesSiteUrlUsesHttpsSchemeForSecureRequests()
    {
        $this->mockCompilerRoute('');
        $_SERVER['HTTP_HOST'] = 'hyde.test';
        $_SERVER['HTTPS'] = 'on';

        $this->invokeOverrideSiteUrl();

        $this->assertSame('https://hyde.test', config('hyde.url'));
    }

    public function testOverridesSiteUrlFallsBackToConfiguredServerWhenHostIsMissing()
    {
        $this->mockCompilerRoute('');
        unset($_SERVER['HTTP_HOST']);

        config([
            'hyde.server.host' => 'hyde.test',
            'hyde.server.port' => 8080,
        ]);

        $this->invokeOverrideSiteUrl();

        $this->assertSame('http://hyde.test:8080', config('hyde.url'));
    }

    public function testOverridesSiteUrlFallsBackToConfiguredServerWhenHostHeaderIsInvalid()
    {
        $this->mockCompilerRoute('');
        $_SERVER['HTTP_HOST'] = 'evil.test/path';

        config([
            'hyde.server.host' => 'localhost',
            'hyde.server.port' => 8080,
        ]);

        $this->invokeOverrideSiteUrl();

        $this->assertSame('http://localhost:8080', config('hyde.url'));
    }

    public function testDoesNotOverrideSiteUrlWhenSavePreviewIsEnabled()
    {
        $this->mockCompilerRoute('');
        $_SERVER['HTTP_HOST'] = 'localhost:8080';

        config([
            'hyde.server.save_preview' => true,
            'hyde.url' => 'https://hydephp.com',
        ]);

        $this->invokeOverrideSiteUrl();

        $this->assertSame('https://hydephp.com', config('hyde.url'));
    }

    public function testRouterHandleOverridesSiteUrlForPageRequest()
    {
        putenv('SERVER_DASHBOARD=false');
        $this->mockCompilerRoute('');
        $_SERVER['HTTP_HOST'] = 'localhost:8080';

        config(['hyde.url' => 'https://hydephp.com']);

        $kernel = new HttpKernel();
        $kernel->handle(new Request());

        $this->assertSame('http://localhost:8080', config('hyde.url'));
    }

    public function testRouterHandleDoesNotOverrideSiteUrlWhenSavePreviewIsEnabled()
    {
        putenv('SERVER_DASHBOARD=false');
        putenv('SERVER_SAVE_PREVIEW=true');
        $this->mockCompilerRoute('');
        $_SERVER['HTTP_HOST'] = 'localhost:8080';

        $kernel = new HttpKernel();
        $kernel->handle(new Request());

        // When save_preview is enabled, overrideSiteUrl() must not replace the
        // configured URL with the local server address.
        $this->assertNotSame('http://localhost:8080', config('hyde.url'));

        putenv('SERVER_SAVE_PREVIEW=');
    }

    protected function mockCompilerRoute(string $route, $method = 'GET'): void
    {
        $_SERVER['REQUEST_METHOD'] = $method;
        $_SERVER['REQUEST_URI'] = "/$route";
    }

    protected function invokeOverrideSiteUrl(): void
    {
        $method = new ReflectionMethod(Router::class, 'overrideSiteUrl');
        $method->setAccessible(true);
        $method->invoke(new Router(new Request()));
    }

    protected function invokeGetContentType(InMemoryPage $page): string
    {
        $this->mockCompilerRoute('foo');

        $method = new ReflectionMethod(PageRouter::class, 'getContentType');
        $method->setAccessible(true);

        return $method->invoke(new PageRouter(new Request()), $page);
    }

    protected function makePageWithOutputPath(string $outputPath): InMemoryPage
    {
        return new class('foo', [], 'contents', '', $outputPath) extends InMemoryPage
        {
            protected string $customOutputPath;

            public function __construct(string $identifier, array $matter, string $contents, string $view, string $outputPath)
            {
                // The custom output path must be assigned before calling the parent
                // constructor, as it triggers factory data construction which calls
                // getOutputPath() before this constructor body would otherwise run.
                $this->customOutputPath = $outputPath;

                parent::__construct($identifier, $matter, $contents, $view);
            }

            public function getOutputPath(): string
            {
                return $this->customOutputPath;
            }
        };
    }

    protected function getResponseHeaders(Response $response): array
    {
        $property = new ReflectionProperty(Response::class, 'headers');
        $property->setAccessible(true);

        return $property->getValue($response);
    }
}
