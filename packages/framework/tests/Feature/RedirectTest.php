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

        $this->assertSame("<!DOCTYPE html>\n<html lang=\"en\">\n    <head>\n        <meta charset=\"UTF-8\" />\n        <meta http-equiv=\"refresh\" content=\"0;url='bar'\" />\n        <style>@media (prefers-color-scheme:dark){html{background-color:#111827;color:white;}}</style>\n\n        <title>Redirecting to bar</title>\n    </head>\n    <body>\n        Redirecting to <a href=\"bar\">bar</a>.\n    </body>\n</html>\n",
            str_replace("\r", '', $redirect->render())
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
}
