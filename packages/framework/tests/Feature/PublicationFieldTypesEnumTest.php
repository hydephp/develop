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
            1 => 'boolean',
            2 => 'integer',
            3 => 'float',
            4 => 'datetime',
            5 => 'url',
            6 => 'array',
            7 => 'text',
            8 => 'image',
            9 => 'tag',
        ], PublicationFieldTypes::values());
    }
}
