<?php

namespace Hyde\Framework\Testing\Feature;

use Hyde\Framework\Hyde;
use Hyde\Framework\Models\Pages\Redirect;
use Hyde\Testing\TestCase;

/**
 * @covers \Hyde\Framework\Models\Pages\Redirect
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

        $this->assertSame('', $redirect->render());

        $redirect->store();

        $this->assertFileExists(Hyde::path('_site/foo.html'));
        $this->assertSame('', file_get_contents(Hyde::path('_site/foo.html')));

        unlink(Hyde::path('_site/foo.html'));
    }
}
