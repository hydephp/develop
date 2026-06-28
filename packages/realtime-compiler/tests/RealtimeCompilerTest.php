<?php

declare(strict_types=1);

use Hyde\Testing\TestCase;
use Desilva\Microserve\JsonResponse;
use Desilva\Microserve\Request;
use Desilva\Microserve\Response;
use Hyde\Facades\Filesystem;
use Hyde\Framework\Exceptions\RouteNotFoundException;
use Hyde\RealtimeCompiler\Http\ExceptionHandler;
use Desilva\Microserve\HtmlResponse;
use Hyde\RealtimeCompiler\Http\HttpKernel;
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
}
