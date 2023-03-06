<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit;

use Hyde\Testing\UnitTestCase;
use Hyde\Support\Concerns\Serializable;
use Hyde\Support\Contracts\SerializableContract;

/**
 * @covers \Hyde\Support\Concerns\Serializable
 */
class SerializableTest extends UnitTestCase
{
    public function testToArray()
    {
        $this->assertSame(['foo' => 'bar'], (new SerializableTestClass)->toArray());
    }

    public function testJsonSerialize()
    {
        $this->assertSame(['foo' => 'bar'], (new SerializableTestClass)->jsonSerialize());
    }

    public function testToJson()
    {
        $this->assertSame('{"foo":"bar"}', (new SerializableTestClass)->toJson());
    }

    public function testJsonEncode()
    {
        $this->assertSame('{"foo":"bar"}', json_encode(new SerializableTestClass));
    }
}

class SerializableTestClass implements SerializableContract
{
    use Serializable;

    public function toArray(): array
    {
        return ['foo' => 'bar'];
    }
}
