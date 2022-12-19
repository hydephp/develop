<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit;

use Hyde\Framework\Features\Publications\Models\PublicationFieldType;
use Hyde\Framework\Features\Publications\Models\PublicationType;
use Hyde\Testing\TestCase;

/**
 * @covers \Hyde\Framework\Features\Publications\Models\PublicationFieldType
 */
class PublicationFieldTypeValidationRulesTest extends TestCase
{
    public function testWithArray()
    {
        $rules = (new PublicationFieldType('array', 'myArray', '4', '8'))->getValidationRules();
        $this->assertSame(['array'], $rules->toArray());
    }

    public function testWithDatetime()
    {
        $rules = (new PublicationFieldType('datetime', 'myDatetime', '4', '8'))->getValidationRules();
        $this->assertSame(['after:4', 'before:8'], $rules->toArray());
    }

    public function testWithFloat()
    {
        $rules = (new PublicationFieldType('float', 'myFloat', '4', '8'))->getValidationRules();
        $this->assertSame(['between:4,8'], $rules->toArray());
    }

    public function testWithInteger()
    {
        $rules = (new PublicationFieldType('integer', 'myInteger', '4', '8'))->getValidationRules();
        $this->assertSame(['between:4,8'], $rules->toArray());
    }

    public function testWithString()
    {
        $rules = (new PublicationFieldType('string', 'myString', '4', '8'))->getValidationRules();
        $this->assertSame(['between:4,8'], $rules->toArray());
    }

    public function testWithText()
    {
        $rules = (new PublicationFieldType('text', 'myText', '4', '8'))->getValidationRules();
        $this->assertSame(['between:4,8'], $rules->toArray());
    }

    public function testWithImage()
    {
        $rules = (new PublicationFieldType('image', 'myImage', '4', '8', publicationType: new PublicationType('foo')))->getValidationRules();
        $this->assertSame(['in:'], $rules->toArray());
    }

    public function testWithTag()
    {
        $this->markTestIncomplete('tags are not working yet');
        $rules = (new PublicationFieldType('tag', 'myTag', '4', '8', 'foo'))->getValidationRules();
        $this->assertSame(['in:foo'], $rules->toArray()); // TODO tags are not working
    }

    public function testWithUrl()
    {
        $rules = (new PublicationFieldType('url', 'myUrl', '4', '8'))->getValidationRules();
        $this->assertSame([], $rules->toArray());
    }
}
