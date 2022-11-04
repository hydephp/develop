<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature;

use Hyde\Hyde;
use Hyde\Support\Models\Redirect;
use Hyde\Testing\TestCase;

/**
 * @covers \Hyde\Support\Models\Redirect
 */
class RedirectTest extends TestCase
{
    public function test_can_create_a_redirect()
    {
        $redirect = Redirect::make('foo', 'bar');

        $this->assertInstanceOf(Redirect::class, $redirect);
        $this->assertEquals(new Redirect('foo', 'bar'), $redirect);
        $this->assertSame('foo', $redirect->path);
        $this->assertSame('bar', $redirect->destination);

        $this->assertSame(str_replace("\r", '', <<<HTML
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
            
            HTML), str_replace("\r", '', $redirect->render())
        );

        $this->assertFileExists(Hyde::path('_site/foo.html'));
        $this->assertSame($redirect->render(), file_get_contents(Hyde::path('_site/foo.html')));

        unlink(Hyde::path('_site/foo.html'));
    }

    public function test_path_parameter_is_normalized()
    {
        $redirect = Redirect::make('foo.html', 'bar');

        $this->assertSame('foo', $redirect->path);

        unlink(Hyde::path('_site/foo.html'));
    }

    public function test_text_can_be_disabled()
    {
        $redirect = Redirect::make('foo', 'bar');
        $this->assertStringContainsString('Redirecting to <a href=', $redirect->render());

        $redirect = Redirect::make('foo', 'bar', false);
        $this->assertStringNotContainsString('Redirecting to <a href=', $redirect->render());
    }
}
