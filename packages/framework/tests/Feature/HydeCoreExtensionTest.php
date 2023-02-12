<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature;

use Hyde\Foundation\HydeCoreExtension;
use Hyde\Foundation\HydeKernel;
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

    public function testClassIsRegistered()
    {
        $this->assertContains(HydeCoreExtension::class, HydeKernel::getInstance()->getRegisteredExtensions());
    }
}
