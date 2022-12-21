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
        $rules = (new PublicationField('array', 'myArray'))->getValidationRules();
        $this->assertSame(['array'], $rules->toArray());
    }

    public function testValidateArrayPasses()
    {
        $validated = (new PublicationField('array', 'myArray'))->validate(['foo', 'bar', 'baz']);
        $this->assertSame(['my-array' => ['foo', 'bar', 'baz']], $validated);
    }

    public function testValidateArrayFails()
    {
        $this->expectValidationException('The my-array must be an array.');
        (new PublicationField('array', 'myArray'))->validate('foo');
    }

    public function testGetRulesForDatetime()
    {
        $rules = (new PublicationField('datetime', 'myDatetime'))->getValidationRules();
        $this->assertSame(['date'], $rules->toArray());
    }

    public function testValidateDatetimePasses()
    {
        $validated = (new PublicationField('datetime', 'myDatetime'))->validate('2021-01-01');
        $this->assertSame(['my-datetime' => '2021-01-01'], $validated);
    }

    public function testValidateDatetimeFailsForInvalidType()
    {
        $this->expectValidationException('The my-datetime is not a valid date.');
        (new PublicationField('datetime', 'myDatetime'))->validate('string');
    }

    public function testGetRulesForFloat()
    {
        $rules = (new PublicationField('float', 'myFloat'))->getValidationRules();
        $this->assertSame(['numeric'], $rules->toArray());
    }

    public function testGetRulesForInteger()
    {
        $rules = (new PublicationField('integer', 'myInteger'))->getValidationRules();
        $this->assertSame(['integer', 'numeric'], $rules->toArray());
    }

    public function testGetRulesForString()
    {
        $rules = (new PublicationField('string', 'myString'))->getValidationRules();
        $this->assertSame(['string'], $rules->toArray());
    }

    public function testGetRulesForText()
    {
        $rules = (new PublicationField('text', 'myText'))->getValidationRules();
        $this->assertSame(['string'], $rules->toArray());
    }

    public function testGetRulesForImage()
    {
        $this->directory('_media/foo');
        $this->file('_media/foo/bar.jpg');
        $this->file('_media/foo/baz.png');
        $rules = (new PublicationField('image', 'myImage'))->getValidationRules(publicationType: new PublicationType('foo'));
        $this->assertSame(['in:_media/foo/bar.jpg,_media/foo/baz.png'], $rules->toArray());
    }

    public function testGetRulesForTag()
    {
        $rules = (new PublicationField('tag', 'myTag', tagGroup: 'foo'))->getValidationRules();
        $this->assertSame(['in:'], $rules->toArray());
    }

    public function testGetRulesForUrl()
    {
        $rules = (new PublicationField('url', 'myUrl'))->getValidationRules();
        $this->assertSame(['url'], $rules->toArray());
    }

    protected function expectValidationException(string $message): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage($message);
    }
}
