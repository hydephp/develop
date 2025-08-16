<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature;

use Hyde\Framework\Features\Navigation\NavigationData;
use Hyde\Markdown\Contracts\FrontMatter\SubSchemas\NavigationSchema;
use Hyde\Testing\UnitTestCase;
use ReflectionClass;

#[\PHPUnit\Framework\Attributes\CoversClass(\Hyde\Framework\Features\Navigation\NavigationData::class)]
class NavigationDataTest extends UnitTestCase
{
    protected array $array = [
        'label' => 'label',
        'priority' => 1,
        'hidden' => true,
        'group' => 'group',
    ];

    public function testClassMatchesSchema()
    {
        $this->assertSame(
            NavigationSchema::NAVIGATION_SCHEMA,
            $this->getImplementedSchema(NavigationData::class)
        );
    }

    public function testConstruct()
    {
        $navigationData = new NavigationData('label', 1, true, 'group');

        $this->assertSame('label', $navigationData->label);
        $this->assertSame('group', $navigationData->group);
        $this->assertSame(1, $navigationData->priority);
        $this->assertTrue($navigationData->hidden);
    }

    public function testConstructWithDifferentData()
    {
        $navigationData = new NavigationData('label', 2, false);

        $this->assertSame('label', $navigationData->label);
        $this->assertSame(2, $navigationData->priority);
        $this->assertFalse($navigationData->hidden);
        $this->assertNull($navigationData->group);
    }

    public function testMake()
    {
        $navigationData = NavigationData::make($this->array);

        $this->assertEquals($navigationData, new NavigationData('label', 1, true, 'group'));
    }

    public function testToArray()
    {
        $this->assertSame($this->array, NavigationData::make($this->array)->toArray());
    }

    public function testJsonSerialize()
    {
        $this->assertSame($this->array, NavigationData::make($this->array)->jsonSerialize());
    }

    protected function getImplementedSchema(string $class): array
    {
        $reflection = new ReflectionClass($class);

        $schema = [];

        foreach (get_class_vars($class) as $name => $void) {
            $schema[$name] = $reflection->getProperty($name)->getType()->getName();
        }

        return $schema;
    }
}
