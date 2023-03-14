<?php

declare(strict_types=1);

namespace Hyde\Publications\Testing\Feature;

use Hyde\Publications\Actions\PublicationFieldValidator;
use Hyde\Publications\Models\PublicationFieldDefinition;
use Hyde\Publications\Models\PublicationType;
use Hyde\Testing\TestCase;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @covers \Hyde\Publications\Actions\PublicationFieldValidator
 */
class PublicationFieldValidatorTest extends TestCase
{
    public function testConstruct()
    {
        $this->assertInstanceOf(PublicationFieldValidator::class, new PublicationFieldValidator(
            $this->createMock(PublicationType::class),
            $this->createMock(PublicationFieldDefinition::class)
        ));
    }

    public function testValidate()
    {
        $fieldDefinition = new PublicationFieldDefinition('string', 'myString');
        $validated = (new PublicationFieldValidator($this->mockPublicationType(), $fieldDefinition))->validate('foo');
        $this->assertSame(['myString' => 'foo'], $validated);

        $this->expectValidationException('The my string must be a string.');
        $fieldDefinition = new PublicationFieldDefinition('string', 'myString');
        (new PublicationFieldValidator($this->mockPublicationType(), $fieldDefinition))->validate(1);
    }

    public function testValidateWithCustomTypeRules()
    {
        $fieldDefinition = new PublicationFieldDefinition('string', 'myString', ['min:3']);
        $validated = (new PublicationFieldValidator($this->mockPublicationType(), $fieldDefinition))->validate('foo');
        $this->assertSame(['myString' => 'foo'], $validated);

        $this->expectValidationException('The my string must be at least 5 characters.');
        $fieldDefinition = new PublicationFieldDefinition('string', 'myString', ['min:5']);
        (new PublicationFieldValidator($this->mockPublicationType(), $fieldDefinition))->validate('foo');
    }

    public function testGetRules()
    {
        $fieldDefinition = new PublicationFieldDefinition('string', 'myString');
        $rules = (new PublicationFieldValidator($this->mockPublicationType(), $fieldDefinition))->getValidationRules();
        $this->assertSame(['string'], $rules);
    }

    public function testGetRulesWithCustomTypeRules()
    {
        $fieldDefinition = new PublicationFieldDefinition('string', 'myString', ['foo', 'bar']);
        $rules = (new PublicationFieldValidator($this->mockPublicationType(), $fieldDefinition))->getValidationRules();
        $this->assertSame(['string', 'foo', 'bar'], $rules);
    }

    public function testGetRulesForArray()
    {
        $fieldDefinition = new PublicationFieldDefinition('array', 'myArray');
        $rules = (new PublicationFieldValidator($this->mockPublicationType(), $fieldDefinition))->getValidationRules();
        $this->assertSame(['array'], $rules);
    }

    public function testValidateArrayPasses()
    {
        $fieldDefinition = new PublicationFieldDefinition('array', 'myArray');
        $validated = (new PublicationFieldValidator($this->mockPublicationType(), $fieldDefinition))->validate([
            'foo', 'bar', 'baz',
        ]);
        $this->assertSame(['myArray' => ['foo', 'bar', 'baz']], $validated);
    }

    public function testValidateArrayFails()
    {
        $this->expectValidationException('The my array must be an array.');
        $fieldDefinition = new PublicationFieldDefinition('array', 'myArray');
        (new PublicationFieldValidator($this->mockPublicationType(), $fieldDefinition))->validate('foo');
    }

    public function testGetRulesForDatetime()
    {
        $fieldDefinition = new PublicationFieldDefinition('datetime', 'myDatetime');
        $rules = (new PublicationFieldValidator($this->mockPublicationType(), $fieldDefinition))->getValidationRules();
        $this->assertSame(['date'], $rules);
    }

    public function testValidateDatetimePasses()
    {
        $fieldDefinition = new PublicationFieldDefinition('datetime', 'myDatetime');
        $validated = (new PublicationFieldValidator($this->mockPublicationType(), $fieldDefinition))->validate('2021-01-01');
        $this->assertSame(['myDatetime' => '2021-01-01'], $validated);
    }

    public function testValidateDatetimeFailsForInvalidType()
    {
        $this->expectValidationException('The my datetime is not a valid date.');
        $fieldDefinition = new PublicationFieldDefinition('datetime', 'myDatetime');
        (new PublicationFieldValidator($this->mockPublicationType(), $fieldDefinition))->validate('string');
    }

    public function testGetRulesForFloat()
    {
        $fieldDefinition = new PublicationFieldDefinition('float', 'myFloat');
        $rules = (new PublicationFieldValidator($this->mockPublicationType(), $fieldDefinition))->getValidationRules();
        $this->assertSame(['numeric'], $rules);
    }

    public function testGetRulesForInteger()
    {
        $fieldDefinition = new PublicationFieldDefinition('integer', 'myInteger');
        $rules = (new PublicationFieldValidator($this->mockPublicationType(), $fieldDefinition))->getValidationRules();
        $this->assertSame(['integer'], $rules);
    }

    public function testGetRulesForString()
    {
        $fieldDefinition = new PublicationFieldDefinition('string', 'myString');
        $rules = (new PublicationFieldValidator($this->mockPublicationType(), $fieldDefinition))->getValidationRules();
        $this->assertSame(['string'], $rules);
    }

    public function testGetRulesForText()
    {
        $fieldDefinition = new PublicationFieldDefinition('text', 'myText');
        $rules = (new PublicationFieldValidator($this->mockPublicationType(), $fieldDefinition))->getValidationRules();
        $this->assertSame(['string'], $rules);
    }

    public function testGetRulesForMedia()
    {
        $this->directory('_media/foo');
        $this->file('_media/foo/bar.jpg');
        $fieldDefinition = new PublicationFieldDefinition('media', 'myMedia');
        $rules = (new PublicationFieldValidator(new PublicationType('foo'), $fieldDefinition))->getValidationRules();
        $this->assertSame(['string', 'in:_media/foo/bar.jpg'], $rules);
    }

    public function testGetRulesForTag()
    {
        $fieldDefinition = new PublicationFieldDefinition('tag', 'myTag');
        $rules = (new PublicationFieldValidator($this->mockPublicationType(), $fieldDefinition))->getValidationRules();
        $this->assertSame(['in:'], $rules);
    }

    public function testGetRulesForUrl()
    {
        $fieldDefinition = new PublicationFieldDefinition('url', 'myUrl');
        $rules = (new PublicationFieldValidator($this->mockPublicationType(), $fieldDefinition))->getValidationRules();
        $this->assertSame(['url'], $rules);
    }

    protected function expectValidationException(string $message): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage($message);
    }

    protected function mockPublicationType(): PublicationType|MockObject
    {
        return $this->createMock(PublicationType::class);
    }
}
