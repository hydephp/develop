<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit;

use Hyde\Framework\Factories\HydePageDataFactory;
use Hyde\Pages\Concerns\HydePage;
use Hyde\Pages\InMemoryPage;
use Hyde\Testing\UnitTestCase;
use Illuminate\Config\Repository;
use Illuminate\Support\Facades\Config;

/**
 * @covers \Hyde\Framework\Factories\HydePageDataFactory
 */
class HydePageDataFactoryTest extends UnitTestCase
{
    public static function setUpBeforeClass(): void
    {
        self::needsKernel();
        self::mockConfig([
            'hyde' => [
                //
            ],
        ]);
    }

    public function testCanConstruct()
    {
        $this->assertInstanceOf(HydePageDataFactory::class, $this->factory());
    }

    public function testToArrayContainsExpectedKeys()
    {
        $this->assertSame(['title', 'canonicalUrl', 'navigation'], array_keys($this->factory()->toArray()));
    }

    protected static function mockConfig(array $items): void
    {
        app()->bind('config', function () use ($items) {
            return new Repository($items);
        });

        Config::swap(app('config'));
    }

    protected function factory(array $data = [], HydePage $page = null): HydePageDataFactory
    {
        return new HydePageDataFactory(($page ?? new InMemoryPage('', $data))->toCoreDataObject());
    }
}
