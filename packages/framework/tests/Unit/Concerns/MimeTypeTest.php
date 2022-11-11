<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit\Concerns;

use Hyde\Support\Filesystem\MimeType;
use Hyde\Testing\TestCase;

/**
 * @covers \Hyde\Support\Filesystem\MimeType
 */
class MimeTypeTest extends TestCase
{
    public function test_can_get_the_mime_types()
    {
        $this->assertSame('text/plain', \Hyde\Support\Filesystem\MimeType::txt->value);
        $this->assertSame('text/html', MimeType::html->value());
    }

    public function test_can_check_if_mime_type_exists()
    {
        $this->assertTrue(\Hyde\Support\Filesystem\MimeType::has('txt'));
        $this->assertTrue(MimeType::has('html'));
        $this->assertFalse(\Hyde\Support\Filesystem\MimeType::has('foo'));
    }

    public function test_can_get_the_mime_type_from_extension()
    {
        $this->assertSame('text/plain', \Hyde\Support\Filesystem\MimeType::get('txt')->value());
        $this->assertSame('text/html', \Hyde\Support\Filesystem\MimeType::get('html')->value());
    }

    public function test_match_method_maps_extensions_to_mime_types()
    {
        $this->assertSame('text/plain', MimeType::match('foo.txt'));
        $this->assertSame('text/markdown', MimeType::match('.md'));
        $this->assertSame('text/markdown', \Hyde\Support\Filesystem\MimeType::match('md'));
        $this->assertSame('text/plain', MimeType::match('invalid'));
    }

    public function test_match_method_default_value_can_be_set()
    {
        $this->assertSame('foo', \Hyde\Support\Filesystem\MimeType::match('invalid', 'foo'));
    }

    public function test_match_method_default_value_can_be_set_to_null()
    {
        $this->assertNull(\Hyde\Support\Filesystem\MimeType::match('invalid', null));
    }
}
