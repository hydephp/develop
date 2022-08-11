<?php

namespace Hyde\Framework\Testing\Unit;

use Hyde\Framework\Concerns\JsonSerializesArrayable;
use Hyde\Testing\TestCase;

/**
 * @covers \Hyde\Framework\Concerns\JsonSerializesArrayable
 */
class JsonSerializesArrayableTest extends TestCase
{
    public function test_json_serialize()
    {
        $class = new class implements \JsonSerializable {
            use JsonSerializesArrayable;

            public function toArray()
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
