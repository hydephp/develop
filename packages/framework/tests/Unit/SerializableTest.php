<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit;

use Hyde\Support\Concerns\Serializable;
use Hyde\Testing\TestCase;
use JsonSerializable;

/**
 * @covers \Hyde\Support\Concerns\Serializable
 */
class SerializableTest extends TestCase
{
    public function test_json_serialize()
    {
        $class = new class implements JsonSerializable
        {
            use Serializable;

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

    public function test_to_json()
    {
        $class = new class
        {
            use Serializable;

            public function toArray(): array
            {
                return ['foo' => 'bar'];
            }
        };

        $this->assertEquals('{"foo":"bar"}', $class->toJson());
    }
}
