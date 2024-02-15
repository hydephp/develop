<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature;

use Hyde\Testing\TestCase;
use Hyde\Framework\Features\Navigation\NavItem;
use Hyde\Framework\Features\Navigation\NavigationMenu;
use Hyde\Framework\Features\Navigation\GeneratesMainNavigationMenu;

/**
 * High-level broad-spectrum tests for the automatic navigation configurations, testing various setups.
 *
 * @covers \Hyde\Framework\Factories\NavigationDataFactory
 * @covers \Hyde\Framework\Features\Navigation\DocumentationSidebar
 * @covers \Hyde\Framework\Features\Navigation\GeneratesDocumentationSidebarMenu
 * @covers \Hyde\Framework\Features\Navigation\GeneratesMainNavigationMenu
 * @covers \Hyde\Framework\Features\Navigation\NavItem
 */
class AutomaticNavigationConfigurationsTest extends TestCase
{
    public function testMainNavigationMenu()
    {
        $this->menu()
            ->assertHasItem('Home')
            ->assertEquals([
                [
                    'label' => 'Home',
                    'group' => null,
                    'priority' => 0,
                    'children' => [],
                ],
            ]);
    }

    protected function menu(): AssertableNavigationMenu
    {
        return new AssertableNavigationMenu($this);
    }
}

class TestNavItem
{
    public readonly string $label;
    public readonly ?string $group;
    public readonly int $priority;
    public readonly array $children;

    public function __construct(string $label, ?string $group, int $priority, array $children)
    {
        $this->label = $label;
        $this->group = $group;
        $this->priority = $priority;
        $this->children = collect($children)->map(fn (NavItem $child) => $child->getLabel())->toArray();
    }

    public static function properties(): array
    {
        return ['label', 'group', 'priority', 'children'];
    }
}

class AssertableNavigationMenu extends NavigationMenu
{
    protected TestCase $test;

    public function __construct(TestCase $test)
    {
        parent::__construct(GeneratesMainNavigationMenu::handle()->getItems());

        $this->test = $test;
    }

    /** A simplified serialized format for comparisons */
    public function state(): array
    {
        return $this->items->map(function (NavItem $item): TestNavItem {
            return new TestNavItem($item->getLabel(), $item->getGroup(), $item->getPriority(), $item->getChildren());
        })->toArray();
    }

    /** @noinspection PhpUnused, PhpNoReturnAttributeCanBeAddedInspection */
    public function dd(): void
    {
        dd($this->items);
    }

    /** @noinspection PhpUnused, PhpNoReturnAttributeCanBeAddedInspection */
    public function ddFormat(): void
    {
        dd($this->state());
    }

    /**
     * @param  array  $expected  The expected state format
     * @param  bool  $strict  If false, missing array keys are ignored
     */
    public function assertEquals(array $expected, bool $strict = false): static
    {
        foreach ($expected as $index => $item) {
            foreach (TestNavItem::properties() as $property) {
                $actual = $this->state()[$index] ?? null;

                if ($actual === null) {
                    // Count mismatch which we will catch at end of loop

                    continue;
                }

                if (! isset($item[$property])) {
                    if ($strict) {
                        $this->test->fail("Missing array key '$property' in the expected state");
                    }

                    continue;
                }

                $this->test->assertSame($item[$property], $actual->$property, "Failed to match the expected value for '$property'");
            }

            $this->test->assertCount(count($expected), $this->state(), 'The expected state has a different count than the actual state');
        }

        return $this;
    }

    public function assertHasItem(string $label): static
    {
        $this->test->assertNotEmpty($this->items->first(fn ($item) => $item->getLabel() === $label), "Item with label '$label' not found in the main navigation menu");

        return $this;
    }
}
