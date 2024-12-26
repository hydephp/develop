<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit\Console\Helpers;

use Hyde\Foundation\Providers\ViewServiceProvider;
use Hyde\Testing\UnitTestCase;
use Hyde\Console\Helpers\ViewPublishGroup;

/**
 * @covers \Hyde\Console\Helpers\ViewPublishGroup
 */
class ViewPublishGroupTest extends UnitTestCase
{
    protected static bool $needsKernel = true;
    protected static bool $needsConfig = true;

    protected function setUp(): void
    {
        TestViewPublishGroup::setProvider(TestViewServiceProvider::class);
    }

    protected function tearDown(): void
    {
        TestViewPublishGroup::setProvider(ViewServiceProvider::class);
    }
}

class TestViewServiceProvider extends ViewServiceProvider
{
    public static function pathsToPublish($provider = null, $group = null): array
    {
        return [
            Hyde::vendorPath('src/Foundation/Providers/../../../resources/views/layouts') => Hyde::path('resources/views/vendor/hyde/layouts'),
        ];
    }
}

class TestViewPublishGroup extends ViewPublishGroup
{
    public static function setProvider(string $provider): void
    {
        parent::$provider = $provider;
    }
}
