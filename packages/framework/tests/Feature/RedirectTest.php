<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature;

use Hyde\Facades\Filesystem;
use Hyde\Foundation\HydeKernel;
use Hyde\Framework\Actions\StaticPageBuilder;
use Hyde\Hyde;
use Hyde\Support\Models\Redirect;
use Hyde\Testing\TestCase;

#[\PHPUnit\Framework\Attributes\CoversClass(\Hyde\Support\Models\Redirect::class)]
class RedirectTest extends TestCase
{
    public function testCanCompileARedirect()
    {
        $redirect = new Redirect('foo', 'bar');

        $this->assertSame('foo', $redirect->path);
        $this->assertSame('bar', $redirect->destination);

        $this->assertSame(str_replace("\r", '', <<<'HTML'
            <!DOCTYPE html>
            <html lang="en">
                <head>
                    <meta charset="UTF-8" />
                    <meta http-equiv="refresh" content="0;url='bar'" />
                    <style>@media (prefers-color-scheme:dark){html{background-color:#111827;color:white;}a{color:#9e9eff}}</style>

                    <title>Redirecting to bar</title>
                </head>
            <body>
                Redirecting to <a href="bar">bar</a>.
            </body>
            </html>

            HTML), str_replace("\r", '', $redirect->compile())
        );
    }

    public function testPathParameterIsNormalized()
    {
        $redirect = new Redirect('foo.html', 'bar');

        $this->assertSame('foo', $redirect->path);
    }

    public function testConfiguredRedirectsAreRegisteredWithTheKernelAndBuiltWithTheSite()
    {
        config(['hyde.redirects' => ['foo' => 'bar']]);
        HydeKernel::setInstance(new HydeKernel(Hyde::path()));

        $redirect = Hyde::pages()->get('foo');

        $this->assertEquals(new Redirect('foo', 'bar'), $redirect);
        $this->assertSame($redirect, Hyde::routes()->get('foo')->getPage());
        StaticPageBuilder::handle($redirect);

        $this->assertFileExists(Hyde::path('_site/foo.html'));
        $this->assertSame($redirect->compile(), file_get_contents(Hyde::path('_site/foo.html')));

        Filesystem::unlink('_site/foo.html');
    }

    public function testDottedRedirectPathUsesHtmlPageSemantics()
    {
        $redirect = new Redirect('legacy.json', 'new-location');

        $this->assertSame('legacy.json', $redirect->getRouteKey());
        $this->assertSame('legacy.json.html', $redirect->getOutputPath());
    }

    public function testRedirectsCannotWriteOutsideTheBuildPipeline()
    {
        $this->assertFalse(method_exists(Redirect::class, 'create'));
        $this->assertFalse(method_exists(Redirect::class, 'store'));
    }

    public function testRedirectsAreHiddenFromNavigation()
    {
        $this->assertFalse((new Redirect('foo', 'bar'))->showInNavigation());
    }

    public function testRedirectsAreHiddenFromSitemaps()
    {
        $this->assertFalse((new Redirect('foo', 'bar'))->showInSitemap());
    }
}
