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
        $this->assertEquals('text/plain', MimeType::txt->value);
    }
}
