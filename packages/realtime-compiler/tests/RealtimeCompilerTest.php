<?php

declare(strict_types=1);

use Hyde\Testing\TestCase;
use Desilva\Microserve\JsonResponse;
use Desilva\Microserve\Request;
use Desilva\Microserve\Response;
use Hyde\Hyde;
use Hyde\Facades\Filesystem;
use Hyde\Pages\InMemoryPage;
use Illuminate\View\ViewException;
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

    public function testSends404ErrorResponseForMissingMediaAsset()
    {
        $this->mockCompilerRoute('media/missing.css');

        $kernel = new HttpKernel();
        $response = $kernel->handle(new Request());

        $this->assertInstanceOf(Response::class, $response);
        $this->assertSame(404, $response->statusCode);
        $this->assertSame('Not Found', $response->statusMessage);
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

    public function testThrowsRouteNotFoundExceptionForMissingHtmlPage()
    {
        $this->mockCompilerRoute('missing.html');

        $kernel = new HttpKernel();

        $this->expectException(RouteNotFoundException::class);
        $this->expectExceptionMessage('Route [missing] not found');

        $kernel->handle(new Request());
    }

    public function testFallsBackToPageRouterForExtensionLikePathThatIsNotAnAsset()
    {
        $this->mockCompilerRoute('9.x');

        Filesystem::ensureDirectoryExists('_pages/9.x');
        Filesystem::put('_pages/9.x/index.md', '# Hello World!');

        $kernel = new HttpKernel();
        $response = $kernel->handle(new Request());

        $this->assertInstanceOf(Response::class, $response);
        $this->assertSame(200, $response->statusCode);
        $this->assertStringContainsString('Hello World!', $response->body);

        Filesystem::deleteDirectory('_pages/9.x');
    }

    public function testErrorThrownWhileCompilingExistingDottedPageIsNotSentAsA404()
    {
        // The dotted path makes the request look like a static asset, but as the page exists,
        // an error thrown while compiling it must surface instead of being masked as a 404.
        $this->mockCompilerRoute('9.x');

        Filesystem::ensureDirectoryExists('_pages/9.x');
        Filesystem::put('_pages/9.x/index.md', "[Blade]: {{ \Hyde\Foundation\Facades\Routes::get('missing-route') }}");

        $kernel = new HttpKernel();

        try {
            $kernel->handle(new Request());

            $this->fail('The error thrown while compiling the page was not sent.');
        } catch (ViewException $exception) {
            // Blade wraps exceptions thrown while rendering a view, so we assert on the underlying one.
            $this->assertInstanceOf(RouteNotFoundException::class, $exception->getPrevious());
            $this->assertStringContainsString('Route [missing-route] not found', $exception->getMessage());
        } finally {
            Filesystem::deleteDirectory('_pages/9.x');
        }
    }

    public function testServesRegisteredPageRouteEvenWhenMatchingAssetExists()
    {
        $this->mockCompilerRoute('9.x');

        Filesystem::ensureDirectoryExists('_pages/9.x');
        Filesystem::put('_pages/9.x/index.md', '# Hello World!');
        Filesystem::put('_media/9.x', 'static decoy');

        try {
            $kernel = new HttpKernel();
            $response = $kernel->handle(new Request());

            $this->assertSame(200, $response->statusCode);
            $this->assertStringContainsString('Hello World!', $response->body);
            $this->assertStringNotContainsString('static decoy', $response->body);
        } finally {
            Filesystem::deleteDirectory('_pages/9.x');
            Filesystem::unlink('_media/9.x');
        }
    }

    public function testDocsSearchJsonRouteWinsOverMatchingAssetFile()
    {
        $this->mockCompilerRoute('docs/search.json');

        Filesystem::put('_docs/index.md', '# Hello World!');
        Filesystem::ensureDirectoryExists('_media/docs');
        Filesystem::put('_media/docs/search.json', '"static decoy"');

        try {
            $kernel = new HttpKernel();
            $response = $kernel->handle(new Request());

            $this->assertSame(200, $response->statusCode);
            $this->assertNotSame('"static decoy"', $response->body);
            $this->assertIsArray(json_decode($response->body, true));
        } finally {
            Filesystem::unlink('_docs/index.md');
            Filesystem::deleteDirectory('_media/docs');
        }
    }

    public function testProxiesRootLevelAssetWhenNoRouteMatchesThePath()
    {
        $this->mockCompilerRoute('data.json');

        Filesystem::put('_media/data.json', '{"static": true}');

        try {
            $kernel = new HttpKernel();
            $response = $kernel->handle(new Request());

            $this->assertSame(200, $response->statusCode);
            $this->assertSame('{"static": true}', $response->body);
        } finally {
            Filesystem::unlink('_media/data.json');
        }
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

    public function testSitemapXmlRouteIsServedWithXmlContentType()
    {
        config(['hyde.url' => 'https://example.com']);

        $this->mockCompilerRoute('sitemap.xml');

        $kernel = new HttpKernel();
        $response = $kernel->handle(new Request());

        $this->assertInstanceOf(Response::class, $response);
        $this->assertNotInstanceOf(HtmlResponse::class, $response);
        $this->assertSame(200, $response->statusCode);
        $this->assertSame('OK', $response->statusMessage);

        $headers = $this->getResponseHeaders($response);
        $this->assertSame('application/xml', $headers['Content-Type']);

        $this->assertStringStartsWith('<?xml version="1.0" encoding="UTF-8"?>', $response->body);
        $this->assertStringContainsString('<urlset', $response->body);
    }

    public function testRssFeedRouteIsServedWithXmlContentType()
    {
        config(['hyde.url' => 'https://example.com']);

        $this->mockCompilerRoute('feed.xml');

        Filesystem::put('_posts/rc-test-post.md', '# Hello World!');

        try {
            $kernel = new HttpKernel();
            $response = $kernel->handle(new Request());

            $this->assertInstanceOf(Response::class, $response);
            $this->assertNotInstanceOf(HtmlResponse::class, $response);
            $this->assertSame(200, $response->statusCode);
            $this->assertSame('OK', $response->statusMessage);

            $headers = $this->getResponseHeaders($response);
            $this->assertSame('application/xml', $headers['Content-Type']);

            $this->assertStringStartsWith('<?xml version="1.0" encoding="UTF-8"?>', $response->body);
            $this->assertStringContainsString('<rss', $response->body);
        } finally {
            Filesystem::unlink('_posts/rc-test-post.md');
        }
    }

    public function testRobotsTxtRouteIsServedWithPlainTextContentType()
    {
        $this->mockCompilerRoute('robots.txt');

        $kernel = new HttpKernel();
        $response = $kernel->handle(new Request());

        $this->assertInstanceOf(Response::class, $response);
        $this->assertNotInstanceOf(HtmlResponse::class, $response);
        $this->assertSame(200, $response->statusCode);
        $this->assertSame('OK', $response->statusMessage);

        $headers = $this->getResponseHeaders($response);
        $this->assertSame('text/plain', $headers['Content-Type']);

        $this->assertSame("User-agent: *\nAllow: /\n\nSitemap: http://localhost:8080/sitemap.xml\n", $response->body);
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

    public function testPingRouteReturnsPingResponse()
    {
        $this->mockCompilerRoute('ping');

        $kernel = new HttpKernel();
        $response = $kernel->handle(new Request());

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertSame(200, $response->statusCode);
        $this->assertSame('OK', $response->statusMessage);
    }

    public function testDashboardLinksAreRootRelativeWhenAccessedWithTrailingSlash()
    {
        $dashboardEnvironment = getenv('SERVER_DASHBOARD');
        putenv('SERVER_DASHBOARD=true');
        $this->mockCompilerRoute('dashboard/');

        Filesystem::put('_pages/foo.md', '# Hello World!');
        Filesystem::put('_media/test.css', 'body {}');

        try {
            $kernel = new HttpKernel();
            $response = $kernel->handle(new Request());

            $this->assertInstanceOf(HtmlResponse::class, $response);
            $this->assertSame(200, $response->statusCode);
            $this->assertSame('OK', $response->statusMessage);
            $this->assertStringContainsString('href="/foo.html"', $response->body);
            $this->assertStringContainsString('href="/media/test.css"', $response->body);
            $this->assertStringNotContainsString('href="foo.html"', $response->body);
            $this->assertStringNotContainsString('href="media/test.css"', $response->body);
        } finally {
            Filesystem::unlink('_pages/foo.md');
            Filesystem::unlink('_media/test.css');
            putenv($dashboardEnvironment === false ? 'SERVER_DASHBOARD' : "SERVER_DASHBOARD=$dashboardEnvironment");
        }
    }

    public function testDashboardRendersDeletePageButtonAndConfirmationModal()
    {
        $dashboardEnvironment = getenv('SERVER_DASHBOARD');
        putenv('SERVER_DASHBOARD=true');
        $this->mockCompilerRoute('dashboard');

        Filesystem::put('_pages/delete-button-test.md', '# Delete Button Test');

        try {
            $kernel = new HttpKernel();
            $response = $kernel->handle(new Request());

            $this->assertInstanceOf(HtmlResponse::class, $response);
            $this->assertStringContainsString('class="btn btn-ghost btn-sm btn-delete delete-page-btn"', $response->body);
            $this->assertStringContainsString('data-route-key="delete-button-test"', $response->body);
            $this->assertStringContainsString('id="deletePageModal"', $response->body);
            $this->assertStringContainsString('name="action" value="deletePage"', $response->body);
        } finally {
            Filesystem::unlinkIfExists('_pages/delete-button-test.md');
            putenv($dashboardEnvironment === false ? 'SERVER_DASHBOARD' : "SERVER_DASHBOARD=$dashboardEnvironment");
        }
    }

    public function testDashboardCanDeleteSourceBackedPage()
    {
        $dashboardEnvironment = getenv('SERVER_DASHBOARD');
        putenv('SERVER_DASHBOARD=true');
        $this->mockCompilerRoute('dashboard', 'POST');

        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
        $_SERVER['HTTP_ACCEPT'] = 'application/json';
        $_SESSION['csrf_token'] = 'delete-page-token';

        Filesystem::put('_pages/delete-action-test.md', '# Delete Action Test');

        try {
            $kernel = new HttpKernel();
            $response = $kernel->handle(new Request([
                '_token' => 'delete-page-token',
                'action' => 'deletePage',
                'routeKey' => 'delete-action-test',
            ]));

            $this->assertInstanceOf(JsonResponse::class, $response);
            $this->assertSame(200, $response->statusCode);
            $this->assertSame('OK', $response->statusMessage);
            $this->assertFalse(Filesystem::exists('_pages/delete-action-test.md'));
        } finally {
            Filesystem::unlinkIfExists('_pages/delete-action-test.md');
            putenv($dashboardEnvironment === false ? 'SERVER_DASHBOARD' : "SERVER_DASHBOARD=$dashboardEnvironment");
        }
    }

    public function testDashboardCreatePageModalSupportsHtmlPages()
    {
        $dashboardEnvironment = getenv('SERVER_DASHBOARD');
        putenv('SERVER_DASHBOARD=true');
        $this->mockCompilerRoute('dashboard');

        try {
            $kernel = new HttpKernel();
            $response = $kernel->handle(new Request());
        } finally {
            putenv($dashboardEnvironment === false ? 'SERVER_DASHBOARD' : "SERVER_DASHBOARD=$dashboardEnvironment");
        }

        $this->assertInstanceOf(HtmlResponse::class, $response);
        $this->assertLessThan(
            strpos($response->body, '<option value="blade-page">BladePage</option>'),
            strpos($response->body, '<option value="html-page">HtmlPage</option>')
        );
        $this->assertStringContainsString("selection === 'html-page'", $response->body);
        $this->assertStringContainsString('HTML content', $response->body);
    }

    public function testDashboardCanCreateHtmlPage()
    {
        $dashboardEnvironment = getenv('SERVER_DASHBOARD');
        putenv('SERVER_DASHBOARD=true');
        $this->mockCompilerRoute('dashboard', 'POST');

        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
        $_SERVER['HTTP_ACCEPT'] = 'application/json';
        $_SESSION['csrf_token'] = 'create-html-page-token';

        try {
            $kernel = new HttpKernel();
            $response = $kernel->handle(new Request([
                '_token' => 'create-html-page-token',
                'action' => 'createPage',
                'pageTypeSelection' => 'html-page',
                'titleInput' => 'dashboard-html-test.html',
                'contentInput' => '<main><h1>Dashboard HTML Test</h1></main>',
            ]));

            $this->assertInstanceOf(JsonResponse::class, $response);
            $this->assertSame(201, $response->statusCode);
            $this->assertSame('Created', $response->statusMessage);
            $this->assertFileExists(Hyde::path('_pages/dashboard-html-test.html'));
            $this->assertSame('<main><h1>Dashboard HTML Test</h1></main>', Filesystem::getContents('_pages/dashboard-html-test.html'));
        } finally {
            Filesystem::unlinkIfExists('_pages/dashboard-html-test.html');
            putenv($dashboardEnvironment === false ? 'SERVER_DASHBOARD' : "SERVER_DASHBOARD=$dashboardEnvironment");
        }
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
