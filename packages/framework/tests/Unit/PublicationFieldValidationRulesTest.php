<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit;

use Hyde\Framework\Features\Publications\Models\PublicationField;
use Hyde\Framework\Features\Publications\Models\PublicationType;
use Hyde\Testing\TestCase;

/**
 * @covers \Hyde\Framework\Features\Publications\Models\PublicationField
 */
class PublicationFieldValidationRulesTest extends TestCase
{
    public function testWithArray()
    {
        $rules = (new PublicationField('array', 'myArray', '4', '8'))->getValidationRules();
        $this->assertSame(['array'], $rules->toArray());
    }

    public function testWithDatetime()
    {
        $rules = (new PublicationField('datetime', 'myDatetime', '4', '8'))->getValidationRules();
        $this->assertSame(['after:4', 'before:8'], $rules->toArray());
    }

    public function testWithFloat()
    {
        $rules = (new PublicationField('float', 'myFloat', '4', '8'))->getValidationRules();
        $this->assertSame(['between:4,8'], $rules->toArray());
    }

    public function testWithInteger()
    {
        $rules = (new PublicationField('integer', 'myInteger', '4', '8'))->getValidationRules();
        $this->assertSame(['between:4,8'], $rules->toArray());
    }

    public function testWithString()
    {
        $rules = (new PublicationField('string', 'myString', '4', '8'))->getValidationRules();
        $this->assertSame(['between:4,8'], $rules->toArray());
    }

    public function testWithText()
    {
        $rules = (new PublicationField('text', 'myText', '4', '8'))->getValidationRules();
        $this->assertSame(['between:4,8'], $rules->toArray());
    }

    public function testWithImage()
    {
        $this->directory('_media/foo');
        $this->file('_media/foo/bar.jpg');
        $this->file('_media/foo/baz.png');
        $rules = (new PublicationField('image', 'myImage', '4', '8', publicationType: new PublicationType('foo')))->getValidationRules();
        $this->assertSame(['in:_media/foo/bar.jpg,_media/foo/baz.png'], $rules->toArray());
    }

    public function testWithTag()
    {
        $rules = (new PublicationField('tag', 'myTag', '4', '8', 'foo'))->getValidationRules();
        $this->assertSame(['in:'], $rules->toArray());
    }

    public function testWithUrl()
    {
        $rules = (new PublicationField('url', 'myUrl', '4', '8'))->getValidationRules();
        $this->assertSame([], $rules->toArray());
    }
}
