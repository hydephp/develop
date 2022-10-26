<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature;

use Hyde\Framework\Contracts\FrontMatter\Support\NavigationSchema;
use Hyde\Framework\Models\Navigation\NavigationData;
use Hyde\Testing\TestCase;

/**
 * @covers \Hyde\Framework\Models\Navigation\NavigationData
 */
class NavigationDataTest extends TestCase
{
    protected array $array = [
        'label' => 'label',
        'group' => 'group',
        'hidden' => true,
        'priority' => 1,
    ];

    public function testClassMatchesSchema()
    {
        $this->assertSame(
            NavigationSchema::NAVIGATION_SCHEMA,
            $this->getImplementedSchema(NavigationData::class)
        );
    }

    public function test__construct()
    {
        $navigationData = new NavigationData('label', 'group', true, 1);

        $this->assertEquals('label', $navigationData->label);
        $this->assertEquals('group', $navigationData->group);
        $this->assertEquals(true, $navigationData->hidden);
        $this->assertEquals(1, $navigationData->priority);
    }

    public function testMake()
    {
        $navigationData = NavigationData::make($this->array);

        $this->assertEquals($navigationData, new NavigationData('label', 'group', true, 1));
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
        $reflection = new \ReflectionClass($class);

        $schema = [];
        foreach (get_class_vars($class) as $name => $void) {
            $schema[$name] = $reflection->getProperty($name)->getType()->getName();
        }

        return $schema;
    }
}
