<?php

declare(strict_types=1);

namespace Hyde\Publications\Testing\Feature;

use Hyde\Testing\TestCase;
use Hyde\Publications\Concerns\PublicationFieldTypes;

/**
 * @covers \Hyde\Publications\Concerns\PublicationFieldTypes
 */
class PublicationFieldTypesEnumTest extends TestCase
{
    public function testCases()
    {
        $this->assertCount(10, PublicationFieldTypes::cases());

        $this->assertSame('string', PublicationFieldTypes::String->value);
        $this->assertSame('boolean', PublicationFieldTypes::Boolean->value);
        $this->assertSame('integer', PublicationFieldTypes::Integer->value);
        $this->assertSame('float', PublicationFieldTypes::Float->value);
        $this->assertSame('datetime', PublicationFieldTypes::Datetime->value);
        $this->assertSame('url', PublicationFieldTypes::Url->value);
        $this->assertSame('array', PublicationFieldTypes::Array->value);
        $this->assertSame('text', PublicationFieldTypes::Text->value);
        $this->assertSame('media', PublicationFieldTypes::Media->value);
        $this->assertSame('tag', PublicationFieldTypes::Tag->value);
    }

    public function testGetRules()
    {
        $this->assertSame(['string'], PublicationFieldTypes::String->rules());
        $this->assertSame(['boolean'], PublicationFieldTypes::Boolean->rules());
        $this->assertSame(['integer'], PublicationFieldTypes::Integer->rules());
        $this->assertSame(['numeric'], PublicationFieldTypes::Float->rules());
        $this->assertSame(['date'], PublicationFieldTypes::Datetime->rules());
        $this->assertSame(['url'], PublicationFieldTypes::Url->rules());
        $this->assertSame(['string'], PublicationFieldTypes::Text->rules());
        $this->assertSame(['array'], PublicationFieldTypes::Array->rules());
        $this->assertSame(['string'], PublicationFieldTypes::Media->rules());
        $this->assertSame([], PublicationFieldTypes::Tag->rules());
    }

    public function testCollectCreatesCollectionOfCases()
    {
        $this->assertEquals(collect(PublicationFieldTypes::cases()), PublicationFieldTypes::collect());
    }

    public function testValuesReturnsArrayOfCaseValues()
    {
        $this->assertSame([
            0 => 'string',
            1 => 'datetime',
            2 => 'boolean',
            3 => 'integer',
            4 => 'float',
            5 => 'array',
            6 => 'media',
            7 => 'text',
            8 => 'tag',
            9 => 'url',
        ], PublicationFieldTypes::values());
    }

    public function testNamesReturnsArrayOfCaseNames()
    {
        $this->assertSame([
            0 => 'String',
            1 => 'Datetime',
            2 => 'Boolean',
            3 => 'Integer',
            4 => 'Float',
            5 => 'Array',
            6 => 'Media',
            7 => 'Text',
            8 => 'Tag',
            9 => 'Url',
        ], PublicationFieldTypes::names());
    }

    public function testCanonicable()
    {
        $this->assertSame([
            PublicationFieldTypes::String,
            PublicationFieldTypes::Datetime,
            PublicationFieldTypes::Integer,
            PublicationFieldTypes::Text,
        ], PublicationFieldTypes::canonicable());
    }

    public function testArrayable()
    {
        $this->assertSame([
            PublicationFieldTypes::Array,
            PublicationFieldTypes::Tag,
        ], PublicationFieldTypes::arrayable());
    }

    public function testIsCanonicable()
    {
        foreach (PublicationFieldTypes::canonicable() as $type) {
            $this->assertTrue($type->isCanonicable());
        }
    }

    public function testIsArrayable()
    {
        foreach (PublicationFieldTypes::arrayable() as $type) {
            $this->assertTrue($type->isArrayable());
        }
    }
}
