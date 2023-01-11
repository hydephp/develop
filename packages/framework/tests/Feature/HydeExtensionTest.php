<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature;

use Hyde\Foundation\Concerns\HydeExtension;
use Hyde\Pages\Concerns\HydePage;
use Hyde\Testing\TestCase;

/**
 * @covers \Hyde\Foundation\Concerns\HydeExtension
 */
class HydeExtensionTest extends TestCase
{
    //
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
