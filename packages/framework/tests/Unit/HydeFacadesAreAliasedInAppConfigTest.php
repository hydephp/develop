<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit;

use Hyde\Hyde;
use Hyde\Testing\TestCase;

class HydeFacadesAreAliasedInAppConfigTest extends TestCase
{
    public function test_all_facades_are_aliased_in_app_config()
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
