<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit\PublicationFieldValueTests;

use Hyde\Framework\Features\Publications\Models\PublicationFieldValues\StringField;
use Hyde\Framework\Features\Publications\PublicationFieldTypes;
use Hyde\Testing\TestCase;
use Symfony\Component\Yaml\Yaml;

/**
 * @covers \Hyde\Framework\Features\Publications\Models\PublicationFieldValues\StringField
 */
class StringFieldValueTest extends TestCase
{
    public function testConstruct()
    {
        $value = new StringField('foo');
        $this->assertSame('foo', $value->getValue());
    }

    public function testGetValue()
    {
        $value = new StringField('foo');
        $this->assertSame('foo', $value->getValue());
    }

    public function testGetType()
    {
        $this->assertSame(StringField::TYPE, StringField::getType());
        $this->assertSame(PublicationFieldTypes::String, StringField::getType());
    }

    public function testParseInput()
    {
        $value = StringField::parseInput('foo');
        $this->assertSame('foo', $value);
    }

    public function testToYamlType()
    {
        $value = StringField::toYamlType('foo');
        $this->assertSame('foo', $value);
    }

    public function testToYaml()
    {
        $value = StringField::toYamlType('foo');
        $this->assertSame('foo', Yaml::dump($value));
    }
}
