<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit;

use Hyde\Framework\Features\Publications\Models\PublicationField;
use Hyde\Framework\Features\Publications\Models\PublicationType;
use Hyde\Testing\TestCase;
use Illuminate\Validation\ValidationException;

/**
 * @covers \Hyde\Framework\Features\Publications\Models\PublicationField
 */
class PublicationFieldValidationRulesTest extends TestCase
{
    public function testGetRulesForArray()
    {
        $rules = (new PublicationField('array', 'myArray', '4', '8'))->getValidationRules();
        $this->assertSame(['array'], $rules->toArray());
    }

    public function testValidateArrayPasses()
    {
        $validated = (new PublicationField('array', 'myArray', '4', '8'))->validate(['foo', 'bar', 'baz']);
        $this->assertSame(['my-array' => ['foo', 'bar', 'baz']], $validated);
    }

    public function testValidateArrayFails()
    {
        $this->expectValidationException('The my-array must be an array.');
        (new PublicationField('array', 'myArray', '4', '8'))->validate('foo');
    }

    public function testGetRulesForDatetime()
    {
        $rules = (new PublicationField('datetime', 'myDatetime', '2021-01-01', '2022-01-01'))->getValidationRules();
        $this->assertSame(['after:2021-01-01 00:00:00', 'before:2022-01-01 00:00:00'], $rules->toArray());

        $rules = (new PublicationField('datetime', 'myDatetime', '2021-01-01'))->getValidationRules();
        $this->assertSame(['after:2021-01-01 00:00:00'], $rules->toArray());

        $rules = (new PublicationField('datetime', 'myDatetime', null, '2022-01-01'))->getValidationRules();
        $this->assertSame(['before:2022-01-01 00:00:00'], $rules->toArray());
    }

    public function testGetRulesForFloat()
    {
        $rules = (new PublicationField('float', 'myFloat', '4', '8'))->getValidationRules();
        $this->assertSame(['between:4,8'], $rules->toArray());
    }

    public function testGetRulesForInteger()
    {
        $rules = (new PublicationField('integer', 'myInteger', '4', '8'))->getValidationRules();
        $this->assertSame(['between:4,8'], $rules->toArray());
    }

    public function testGetRulesForString()
    {
        $rules = (new PublicationField('string', 'myString', '4', '8'))->getValidationRules();
        $this->assertSame(['between:4,8'], $rules->toArray());
    }

    public function testGetRulesForText()
    {
        $rules = (new PublicationField('text', 'myText', '4', '8'))->getValidationRules();
        $this->assertSame(['between:4,8'], $rules->toArray());
    }

    public function testGetRulesForImage()
    {
        $this->directory('_media/foo');
        $this->file('_media/foo/bar.jpg');
        $this->file('_media/foo/baz.png');
        $rules = (new PublicationField('image', 'myImage', '4', '8', publicationType: new PublicationType('foo')))->getValidationRules();
        $this->assertSame(['in:_media/foo/bar.jpg,_media/foo/baz.png'], $rules->toArray());
    }

    public function testGetRulesForTag()
    {
        $rules = (new PublicationField('tag', 'myTag', '4', '8', 'foo'))->getValidationRules();
        $this->assertSame(['in:'], $rules->toArray());
    }

    public function testGetRulesForUrl()
    {
        $rules = (new PublicationField('url', 'myUrl', '4', '8'))->getValidationRules();
        $this->assertSame(['url'], $rules->toArray());
    }

    protected function expectValidationException(string $message): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage($message);
    }
}
