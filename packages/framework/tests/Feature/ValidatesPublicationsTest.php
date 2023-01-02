<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature;

use Hyde\Framework\Features\Publications\Models\PublicationFieldDefinition;
use Hyde\Framework\Features\Publications\Models\PublicationType;
use Hyde\Framework\Features\Publications\ValidatesPublicationField;
use Hyde\Testing\TestCase;

use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\MockObject\MockObject;

use function collect;

/**
 * @covers \Hyde\Framework\Features\Publications\ValidatesPublicationField
 */
class ValidatesPublicationsTest extends TestCase
{
    public function testConstruct()
    {
        $this->assertInstanceOf(ValidatesPublicationField::class, new ValidatesPublicationField(
            $this->createMock(PublicationType::class),
            $this->createMock(PublicationFieldDefinition::class)
        ));
    }

    public function testValidate()
    {
        $fieldDefinition = new PublicationFieldDefinition('string', 'myString');
        $validated = (new ValidatesPublicationField($this->mockPublicationType(), $fieldDefinition))->validate('foo');
        $this->assertSame(['my-string' => 'foo'], $validated);

        $this->expectValidationException('The my-string must be a string.');
        $fieldDefinition = new PublicationFieldDefinition('string', 'myString');
        (new ValidatesPublicationField($this->mockPublicationType(), $fieldDefinition))->validate(1);
    }

    public function testValidateWithCustomTypeRules()
    {
        $fieldDefinition = new PublicationFieldDefinition('string', 'myString', ['min:3']);
        $validated = (new ValidatesPublicationField($this->mockPublicationType(), $fieldDefinition))->validate('foo');
        $this->assertSame(['my-string' => 'foo'], $validated);

        $this->expectValidationException('The my-string must be at least 5 characters.');
        $fieldDefinition = new PublicationFieldDefinition('string', 'myString', ['min:5']);
        (new ValidatesPublicationField($this->mockPublicationType(), $fieldDefinition))->validate('foo');
    }

    public function testValidateWithCustomRuleCollection()
    {
        $fieldDefinition = new PublicationFieldDefinition('string', 'myString');
        $validated = (new ValidatesPublicationField($this->mockPublicationType(), $fieldDefinition))->validate('foo', ['min:3']);
        $this->assertSame(['my-string' => 'foo'], $validated);

        $this->expectValidationException('The my-string must be at least 5 characters.');
        $fieldDefinition = new PublicationFieldDefinition('string', 'myString');
        (new ValidatesPublicationField($this->mockPublicationType(), $fieldDefinition))->validate('foo', ['min:5']);
    }

    public function testValidateWithCustomRuleCollectionOverridesDefaultRules()
    {
        $this->expectValidationException('The my-string must be a number.');
        $fieldDefinition = new PublicationFieldDefinition('string', 'myString');
        (new ValidatesPublicationField($this->mockPublicationType(), $fieldDefinition))->validate('foo', ['numeric']);
    }

    public function testValidateMethodAcceptsArrayOfRules()
    {
        $fieldDefinition = new PublicationFieldDefinition('string', 'myString');
        $validated = (new ValidatesPublicationField($this->mockPublicationType(), $fieldDefinition))->validate('foo', ['min:3']);
        $this->assertSame(['my-string' => 'foo'], $validated);
    }

    public function testValidateMethodAcceptsArrayableOfRules()
    {
        $fieldDefinition = new PublicationFieldDefinition('string', 'myString');
        $validated = (new ValidatesPublicationField($this->mockPublicationType(), $fieldDefinition))->validate('foo',
            collect(['min:3']));
        $this->assertSame(['my-string' => 'foo'], $validated);
    }

    public function testGetRules()
    {
        $fieldDefinition = new PublicationFieldDefinition('string', 'myString');
        $rules = (new ValidatesPublicationField($this->mockPublicationType(), $fieldDefinition))->getValidationRules();
        $this->assertSame(['string'], $rules->toArray());
    }

    public function testGetRulesWithCustomTypeRules()
    {
        $fieldDefinition = new PublicationFieldDefinition('string', 'myString', ['foo', 'bar']);
        $rules = (new ValidatesPublicationField($this->mockPublicationType(), $fieldDefinition))->getValidationRules();
        $this->assertSame(['string', 'foo', 'bar'], $rules->toArray());
    }

    public function testGetRulesForArray()
    {
        $fieldDefinition = new PublicationFieldDefinition('array', 'myArray');
        $rules = (new ValidatesPublicationField($this->mockPublicationType(), $fieldDefinition))->getValidationRules();
        $this->assertSame(['array'], $rules->toArray());
    }

    public function testValidateArrayPasses()
    {
        $fieldDefinition = new PublicationFieldDefinition('array', 'myArray');
        $validated = (new ValidatesPublicationField($this->mockPublicationType(), $fieldDefinition))->validate([
            'foo', 'bar', 'baz'
        ]);
        $this->assertSame(['my-array' => ['foo', 'bar', 'baz']], $validated);
    }

    public function testValidateArrayFails()
    {
        $this->expectValidationException('The my-array must be an array.');
        $fieldDefinition = new PublicationFieldDefinition('array', 'myArray');
        (new ValidatesPublicationField($this->mockPublicationType(), $fieldDefinition))->validate('foo');
    }

    public function testGetRulesForDatetime()
    {
        $fieldDefinition = new PublicationFieldDefinition('datetime', 'myDatetime');
        $rules = (new ValidatesPublicationField($this->mockPublicationType(), $fieldDefinition))->getValidationRules();
        $this->assertSame(['date'], $rules->toArray());
    }

    public function testValidateDatetimePasses()
    {
        $fieldDefinition = new PublicationFieldDefinition('datetime', 'myDatetime');
        $validated = (new ValidatesPublicationField($this->mockPublicationType(), $fieldDefinition))->validate('2021-01-01');
        $this->assertSame(['my-datetime' => '2021-01-01'], $validated);
    }

    public function testValidateDatetimeFailsForInvalidType()
    {
        $this->expectValidationException('The my-datetime is not a valid date.');
        $fieldDefinition = new PublicationFieldDefinition('datetime', 'myDatetime');
        (new ValidatesPublicationField($this->mockPublicationType(), $fieldDefinition))->validate('string');
    }

    public function testGetRulesForFloat()
    {
        $fieldDefinition = new PublicationFieldDefinition('float', 'myFloat');
        $rules = (new ValidatesPublicationField($this->mockPublicationType(), $fieldDefinition))->getValidationRules();
        $this->assertSame(['numeric'], $rules->toArray());
    }

    public function testGetRulesForInteger()
    {
        $fieldDefinition = new PublicationFieldDefinition('integer', 'myInteger');
        $rules = (new ValidatesPublicationField($this->mockPublicationType(), $fieldDefinition))->getValidationRules();
        $this->assertSame(['integer', 'numeric'], $rules->toArray());
    }

    public function testGetRulesForString()
    {
        $fieldDefinition = new PublicationFieldDefinition('string', 'myString');
        $rules = (new ValidatesPublicationField($this->mockPublicationType(), $fieldDefinition))->getValidationRules();
        $this->assertSame(['string'], $rules->toArray());
    }

    public function testGetRulesForText()
    {
        $fieldDefinition = new PublicationFieldDefinition('text', 'myText');
        $rules = (new ValidatesPublicationField($this->mockPublicationType(), $fieldDefinition))->getValidationRules();
        $this->assertSame(['string'], $rules->toArray());
    }

    public function testGetRulesForImage()
    {
        $this->directory('_media/foo');
        $this->file('_media/foo/bar.jpg');
        $this->file('_media/foo/baz.png');
        $fieldDefinition = new PublicationFieldDefinition('image', 'myImage');
        $rules = (new ValidatesPublicationField((new PublicationType('foo')), $fieldDefinition))->getValidationRules();
        $this->assertSame(['in:_media/foo/bar.jpg,_media/foo/baz.png'], $rules->toArray());
    }

    public function testGetRulesForTag()
    {
        $fieldDefinition = new PublicationFieldDefinition('tag', 'myTag');
        $rules = (new ValidatesPublicationField($this->mockPublicationType(), $fieldDefinition))->getValidationRules();
        $this->assertSame(['in:'], $rules->toArray());
    }

    public function testGetRulesForUrl()
    {
        $fieldDefinition = new PublicationFieldDefinition('url', 'myUrl');
        $rules = (new ValidatesPublicationField($this->mockPublicationType(), $fieldDefinition))->getValidationRules();
        $this->assertSame(['url'], $rules->toArray());
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
