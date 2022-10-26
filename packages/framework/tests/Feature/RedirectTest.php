<?php
declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature;

use Hyde\Framework\Helpers\Redirect;
use Hyde\Framework\Hyde;
use Hyde\Testing\TestCase;

/**
 * @covers \Hyde\Framework\Helpers\Redirect
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

        $this->assertSame("<!DOCTYPE html>\n<html lang=\"en\">\n    <head>\n        <meta charset=\"UTF-8\" />\n        <meta http-equiv=\"refresh\" content=\"0;url='bar'\" />\n\n        <title>Redirecting to bar</title>\n    </head>\n    <body>\n        Redirecting to <a href=\"bar\">bar</a>.\n    </body>\n</html>",
            str_replace("\r", '', $redirect->render())
        );

        $redirect->store();

        $this->assertFileExists(Hyde::path('_site/foo.html'));
        $this->assertSame($redirect->render(), file_get_contents(Hyde::path('_site/foo.html')));

        unlink(Hyde::path('_site/foo.html'));
    }
}
