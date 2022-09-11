<?php

namespace Hyde\Framework\Testing\Models;

use Hyde\Framework\Models\NavigationData;
use Hyde\Testing\TestCase;

/**
 * @covers \Hyde\Framework\Models\NavigationData
 */
class NavigationDataTest extends TestCase
{
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
        $navigationData = NavigationData::make([
            'label' => 'label',
            'group' => 'group',
            'hidden' => true,
            'priority' => 1,
        ]);

        $this->assertEquals($navigationData, new NavigationData('label', 'group', true, 1));
    }

    public function testLabel()
    {
        $this->assertSame('label', (new NavigationData(label: 'label'))->label());
    }

    public function testGroup()
    {
        $this->assertSame('group', (new NavigationData(group: 'group'))->group());
    }

    public function testHidden()
    {
        $this->assertSame(true, (new NavigationData(hidden: true))->hidden());
        $this->assertSame(false, (new NavigationData(hidden: false))->hidden());
    }

    public function testVisible()
    {
        $this->assertSame(true, (new NavigationData(hidden: false))->visible());
        $this->assertSame(false, (new NavigationData(hidden: true))->visible());
    }

    public function testPriority()
    {
        $this->assertSame(1, (new NavigationData(priority: 1))->priority());
    }

    public function testToArray()
    {
        $this->assertSame($array = [
            'label' => 'label',
            'group' => 'group',
            'hidden' => true,
            'priority' => 1,
        ], NavigationData::make($array)->toArray());
    }

    public function testJsonSerialize()
    {
        $this->assertSame($array = [
            'label' => 'label',
            'group' => 'group',
            'hidden' => true,
            'priority' => 1,
        ], NavigationData::make($array)->jsonSerialize());
    }
}
