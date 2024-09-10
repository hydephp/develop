<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit\Facades;

use Hyde\Hyde;
use Hyde\Testing\UnitTestCase;

class HydeFacadesAreAliasedInAppConfigTest extends UnitTestCase
{
    protected static bool $needsKernel = true;

    protected function setUp(): void
    {
        self::mockConfig(['app' => require Hyde::path('app/config.php')]);
    }

    public function testAllFacadesAreAliasedInAppConfig()
    {
        $this->assertArrayHasKey('Hyde', config('app.aliases'));

        foreach ($this->getFacades() as $facade) {
            $this->assertArrayHasKey($facade, config('app.aliases'));
        }
    }

    protected function getFacades(): array
    {
        return array_map(function (string $facadeClass) {
            return basename($facadeClass, '.php');
        }, glob(Hyde::vendorPath('src/Facades/*.php')));
    }
}
