<?php

declare(strict_types=1);

use Hyde\Testing\UnitTestCase;
use Hyde\Framework\Features\Navigation\NavigationMenuConfigurationBuilder;
use Hyde\Facades\Navigation;

/**
 * @covers \Hyde\Framework\Features\Navigation\NavigationMenuConfigurationBuilder
 */
class NavigationMenuConfigurationBuilderTest extends UnitTestCase
{
    private NavigationMenuConfigurationBuilder $builder;

    protected function setUp(): void
    {
        parent::setUp();
        $this->builder = new NavigationMenuConfigurationBuilder();
    }

    public function testOrder()
    {
        $order = ['index' => 0, 'posts' => 10, 'docs/index' => 100];
        $result = $this->builder->order($order)->toArray();

        $this->assertArrayHasKey('order', $result);
        $this->assertEquals($order, $result['order']);
    }

    public function testLabels()
    {
        $labels = ['index' => 'Home', 'docs/index' => 'Docs'];
        $result = $this->builder->labels($labels)->toArray();

        $this->assertArrayHasKey('labels', $result);
        $this->assertEquals($labels, $result['labels']);
    }

    public function testExclude()
    {
        $exclude = ['404', 'admin'];
        $result = $this->builder->exclude($exclude)->toArray();

        $this->assertArrayHasKey('exclude', $result);
        $this->assertEquals($exclude, $result['exclude']);
    }

    public function testCustom()
    {
        $custom = [
            Navigation::item('https://example.com', 'Example', 200),
            Navigation::item('https://github.com', 'GitHub', 300),
        ];
        $result = $this->builder->custom($custom)->toArray();

        $this->assertArrayHasKey('custom', $result);
        $this->assertEquals($custom, $result['custom']);
    }

    public function testSubdirectoryDisplay()
    {
        $displayModes = ['dropdown', 'flat', 'hidden'];

        foreach ($displayModes as $mode) {
            $result = $this->builder->subdirectoryDisplay($mode)->toArray();

            $this->assertArrayHasKey('subdirectory_display', $result);
            $this->assertEquals($mode, $result['subdirectory_display']);
        }
    }

    public function testChainedMethods()
    {
        $result = $this->builder
            ->order(['index' => 0, 'posts' => 10])
            ->labels(['index' => 'Home'])
            ->exclude(['404'])
            ->custom([Navigation::item('https://github.com', 'GitHub', 200)])
            ->subdirectoryDisplay('dropdown')
            ->toArray();

        $this->assertArrayHasKey('order', $result);
        $this->assertArrayHasKey('labels', $result);
        $this->assertArrayHasKey('exclude', $result);
        $this->assertArrayHasKey('custom', $result);
        $this->assertArrayHasKey('subdirectory_display', $result);
    }

    public function testEmptyConfiguration()
    {
        $result = $this->builder->toArray();

        $this->assertEmpty($result);
    }

    public function testInvalidSubdirectoryDisplay()
    {
        $this->expectException(\TypeError::class);
        $this->builder->subdirectoryDisplay('invalid');
    }

    public function testRealLifeUsageScenario()
    {
        $result = $this->builder
            ->order([
                'index' => 0,
                'posts' => 10,
                'docs/index' => 100,
            ])
            ->labels([
                'index' => 'Home',
                'docs/index' => 'Docs',
            ])
            ->exclude([
                '404',
            ])
            ->custom([
                Navigation::item('https://github.com/hydephp/hyde', 'GitHub', 200),
            ])
            ->subdirectoryDisplay('dropdown')
            ->toArray();

        $this->assertEquals([
            'order' => ['index' => 0, 'posts' => 10, 'docs/index' => 100],
            'labels' => ['index' => 'Home', 'docs/index' => 'Docs'],
            'exclude' => ['404'],
            'custom' => [
                ['destination' => 'https://github.com/hydephp/hyde', 'label' => 'GitHub', 'priority' => 200, 'attributes' => []],
            ],
            'subdirectory_display' => 'dropdown',
        ], $result);
    }

    public function testArrayableInterface()
    {
        $this->assertInstanceOf(\Illuminate\Contracts\Support\Arrayable::class, $this->builder);
    }

    public function testArrayObjectMethods()
    {
        $this->builder->order(['index' => 0]);
        $this->assertCount(1, $this->builder);
        $this->assertEquals(['order' => ['index' => 0]], $this->builder->getArrayCopy());
    }
}
