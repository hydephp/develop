<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature;

use Hyde\Facades\Filesystem;
use Hyde\Framework\Actions\StaticPageBuilder;
use Hyde\Hyde;
use Hyde\Support\Models\Redirect;
use Hyde\Testing\TestCase;

/**
 * @covers \Hyde\Support\Models\Redirect
 */
class RedirectTest extends TestCase
{
    public function testCanCreateARedirect()
    {
        $redirect = Redirect::create('foo', 'bar');

        $this->assertInstanceOf(Redirect::class, $redirect);
        $this->assertEquals(new Redirect('foo', 'bar'), $redirect);
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

        $this->assertFileExists(Hyde::path('_site/foo.html'));
        $this->assertSame($redirect->compile(), file_get_contents(Hyde::path('_site/foo.html')));

        Filesystem::unlink('_site/foo.html');
    }

    public function testPathParameterIsNormalized()
    {
        $redirect = Redirect::create('foo.html', 'bar');

        $this->assertSame('foo', $redirect->path);

        Filesystem::unlink('_site/foo.html');
    }

    public function testTextCanBeDisabled()
    {
        $redirect = Redirect::create('foo', 'bar');
        $this->assertStringContainsString('Redirecting to <a href=', $redirect->compile());

        $redirect = Redirect::create('foo', 'bar', false);
        $this->assertStringNotContainsString('Redirecting to <a href=', $redirect->compile());
    }

    public function testRedirectPagesCanBeCompilableByStaticSiteThoughManualDiscovery()
    {
        $redirect = new Redirect('foo', 'bar');

        Hyde::pages()->addPage($redirect);

        StaticPageBuilder::handle($redirect);

        $this->assertFileExists(Hyde::path('_site/foo.html'));
        $this->assertSame($redirect->compile(), file_get_contents(Hyde::path('_site/foo.html')));

        Filesystem::unlink('_site/foo.html');
    }
}
