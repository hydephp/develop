<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature;

use Hyde\Foundation\HydeCoreExtension;
use Hyde\Testing\TestCase;

/**
 * @covers \Hyde\Foundation\HydeCoreExtension
 */
class HydeCoreExtensionTest extends TestCase
{
    public function testClassExtendsExtensionClass()
    {
        $this->assertInstanceOf(HydeCoreExtension::class, new HydeCoreExtension());
    }
}
