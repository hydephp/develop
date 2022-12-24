<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature;

use Hyde\Framework\Features\Publications\PublicationFieldTypes;
use Hyde\Testing\TestCase;

/**
 * @covers \Hyde\Framework\Features\Publications\PublicationFieldTypes
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
        $this->assertSame('image', PublicationFieldTypes::Image->value);
        $this->assertSame('tag', PublicationFieldTypes::Tag->value);
    }

    public function testGetRules()
    {
        $this->assertSame(['string'], PublicationFieldTypes::String->rules());
        $this->assertSame(['boolean'], PublicationFieldTypes::Boolean->rules());
        $this->assertSame(['integer', 'numeric'], PublicationFieldTypes::Integer->rules());
        $this->assertSame(['numeric'], PublicationFieldTypes::Float->rules());
        $this->assertSame(['date'], PublicationFieldTypes::Datetime->rules());
        $this->assertSame(['url'], PublicationFieldTypes::Url->rules());
        $this->assertSame(['string'], PublicationFieldTypes::Text->rules());
        $this->assertSame(['array'], PublicationFieldTypes::Array->rules());
        $this->assertSame([], PublicationFieldTypes::Image->rules());
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
            5 => 'image',
            6 => 'array',
            7 => 'text',
            8 => 'url',
            9 => 'tag',
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
            5 => 'Image',
            6 => 'Array',
            7 => 'Text',
            8 => 'Url',
            9 => 'Tag',
        ], PublicationFieldTypes::names());
    }

    public function testCanonicable()
    {
        $this->assertSame([
            PublicationFieldTypes::String,
            PublicationFieldTypes::Integer,
            PublicationFieldTypes::Datetime,
            PublicationFieldTypes::Text,
        ], PublicationFieldTypes::canonicable());
    }
}
