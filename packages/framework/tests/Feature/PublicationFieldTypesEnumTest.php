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

    public function testCanGetRulesForEnumWithNoRules()
    {
        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage('This type has no validation rules');
        PublicationFieldTypes::Tag->rules();
    }
}
