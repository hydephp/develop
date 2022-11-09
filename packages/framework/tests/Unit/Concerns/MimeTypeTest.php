<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit\Concerns;

use Hyde\Support\Concerns\MimeType;
use Hyde\Testing\TestCase;

/**
 * @covers \Hyde\Support\Concerns\MimeType
 */
class MimeTypeTest extends TestCase
{
    public function test_can_get_the_mime_types()
    {
        $this->assertSame('text/plain', MimeType::txt->value);
        $this->assertSame('text/html', MimeType::html->value());
    }

    public function test_can_check_if_mime_type_exists()
    {
        $this->assertTrue(MimeType::has('txt'));
        $this->assertTrue(MimeType::has('html'));
        $this->assertFalse(MimeType::has('foo'));
    }

    public function test_can_get_the_mime_type_from_extension()
    {
        $this->assertSame('text/plain', MimeType::get('txt')->value());
        $this->assertSame('text/html', MimeType::get('html')->value());
    }

    public function test_match_method_maps_extensions_to_mime_types()
    {
        $this->assertSame('text/plain', MimeType::match('foo.txt'));
        $this->assertSame('text/markdown', MimeType::match('.md'));
        $this->assertSame('text/markdown', MimeType::match('md'));
        $this->assertSame('text/plain', MimeType::match('invalid'));
    }

    public function test_match_method_default_value_can_be_set()
    {
        $this->assertSame('foo', MimeType::match('invalid', 'foo'));
    }

    public function test_match_method_default_value_can_be_set_to_null()
    {
        $this->assertNull(MimeType::match('invalid', null));
    }
}
