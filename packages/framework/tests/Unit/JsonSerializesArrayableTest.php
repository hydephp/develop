<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit;

use Hyde\Support\Concerns\JsonSerializesArrayable;
use Hyde\Testing\TestCase;

/**
 * @covers \Hyde\Support\Concerns\JsonSerializesArrayable
 */
class JsonSerializesArrayableTest extends TestCase
{
    public function test_json_serialize()
    {
        $class = new class implements \JsonSerializable
        {
            use JsonSerializesArrayable;

            public function toArray(): array
            {
                return ['foo' => 'bar'];
            }
        };

        $this->assertEquals([
            'foo' => 'bar',
        ], $class->toArray());

        $this->assertEquals([
            'foo' => 'bar',
        ], $class->jsonSerialize());

        $this->assertEquals('{"foo":"bar"}', json_encode($class));
    }
}
