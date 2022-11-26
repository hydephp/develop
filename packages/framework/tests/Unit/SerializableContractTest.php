<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit;

use Hyde\Support\Contracts\SerializableContract;
use Hyde\Testing\TestCase;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use JsonSerializable;

/**
 * @see \Hyde\Support\Contracts\SerializableContract
 */
class SerializableContractTest extends TestCase
{
    public function testInterface()
    {
        $this->assertInstanceOf(SerializableContract::class, new SerializableContractTestClass());
    }

    public function testInterfaceExtendsArrayable()
    {
        $this->assertInstanceOf(Arrayable::class, new SerializableContractTestClass());
    }

    public function testInterfaceExtendsJsonable()
    {
        $this->assertInstanceOf(Jsonable::class, new SerializableContractTestClass());
    }

    public function testInterfaceExtendsJsonSerializable()
    {
        $this->assertInstanceOf(JsonSerializable::class, new SerializableContractTestClass());
    }
}

class SerializableContractTestClass implements SerializableContract
{
    public function toArray(): array
    {
        return [];
    }

    public function toJson($options = 0): string
    {
        return '';
    }

    public function jsonSerialize(): array
    {
        return [];
    }
}
