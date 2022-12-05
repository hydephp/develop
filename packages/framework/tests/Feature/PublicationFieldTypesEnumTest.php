<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature;

use BadMethodCallException;
use Hyde\Framework\Features\Publications\Concerns\PublicationFieldTypes;
use Hyde\Testing\TestCase;

/**
 * @covers \Hyde\Framework\Features\Publications\Concerns\PublicationFieldTypes
 */
class PublicationFieldTypesEnumTest extends TestCase
{
    public function testCanGetRulesForEnum()
    {
        $this->assertSame([
            'required',
            'string',
            'between',
        ], PublicationFieldTypes::String->rules());
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

    public function testCanGetRulesForEnumWithNoRules()
    {
        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage('This type has no validation rules');
        PublicationFieldTypes::Tag->rules();
    }
}
