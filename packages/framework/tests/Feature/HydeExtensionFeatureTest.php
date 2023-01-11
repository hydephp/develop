<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature;

use Hyde\Foundation\Concerns\HydeExtension;
use Hyde\Foundation\HydeKernel;
use Hyde\Pages\Concerns\HydePage;
use Hyde\Testing\TestCase;

/**
 * @covers \Hyde\Foundation\Concerns\HydeExtension
 * @covers \Hyde\Foundation\Concerns\ManagesHydeKernel
 * @covers \Hyde\Foundation\HydeKernel
 * @covers \Hyde\Foundation\FileCollection
 * @covers \Hyde\Foundation\PageCollection
 * @covers \Hyde\Foundation\RouteCollection
 */
class HydeExtensionFeatureTest extends TestCase
{
    protected HydeKernel $kernel;

    protected function setUp(): void
    {
        parent::setUp();

        $this->kernel = HydeKernel::getInstance();
    }

    public function testCanRegisterNewExtension()
    {
        $this->kernel->registerExtension(HydeTestExtension::class);
        $this->assertSame([HydeTestExtension::class], $this->kernel->getRegisteredExtensions());
    }
}

class HydeTestExtension extends HydeExtension
{
    public static function getPageClasses(): array
    {
        return [
            HydeExtensionTestPage::class,
        ];
    }
}

class HydeExtensionTestPage extends HydePage
{
    public function compile(): string
    {
        return '';
    }
}
