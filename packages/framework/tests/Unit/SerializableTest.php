<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit;

use Hyde\Testing\UnitTestCase;
use Hyde\Support\Concerns\Serializable;
use Hyde\Support\Contracts\SerializableContract;
use Illuminate\Contracts\Support\Arrayable;

#[\PHPUnit\Framework\Attributes\CoversClass(\Hyde\Support\Concerns\Serializable::class)]
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

    public function testSerialize()
    {
        $this->assertSame(['foo' => 'bar'], (new SerializableTestClass)->arraySerialize());
    }

    public function testSerializeWithArrayable()
    {
        $this->assertSame(['foo' => 'bar', 'arrayable' => ['foo' => 'bar']], (new SerializableTestClassWithArrayable)->arraySerialize());
    }

    public function testJsonSerializeWithArrayable()
    {
        $this->assertSame(['foo' => 'bar', 'arrayable' => ['foo' => 'bar']], (new SerializableTestClassWithArrayable)->jsonSerialize());
    }

    public function testToJsonWithArrayable()
    {
        $this->assertSame('{"foo":"bar","arrayable":{"foo":"bar"}}', (new SerializableTestClassWithArrayable)->toJson());
    }

    public function testAutomaticallySerialization()
    {
        $this->assertSame([
            'foo' => 'foo',
            'bar' => 'bar',
            'baz' => ['baz' => 'baz'],
        ], (new AutomaticallySerializableTestClass)->toArray());
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

class SerializableTestClassWithArrayable implements SerializableContract
{
    use Serializable;

    public function toArray(): array
    {
        return ['foo' => 'bar', 'arrayable' => new ArrayableTestClass()];
    }
}

class ArrayableTestClass implements Arrayable
{
    public function toArray(): array
    {
        return ['foo' => 'bar'];
    }
}

class AutomaticallySerializableTestClass implements SerializableContract
{
    use Serializable;

    public string $foo;
    public string $bar;
    public array $baz;

    public string $uninitialized;

    protected string $hidden;
    private string $private;

    public static string $static;

    public function __construct()
    {
        $this->foo = 'foo';
        $this->bar = 'bar';
        $this->baz = ['baz' => 'baz'];
        $this->hidden = 'hidden';
        $this->private = 'private';
        static::$static = 'static';
    }
}
