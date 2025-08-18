<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit;

use Hyde\Enums\Feature;
use Hyde\Facades\Features;
use Hyde\Hyde;
use Hyde\Testing\UnitTestCase;

#[\PHPUnit\Framework\Attributes\CoversClass(\Hyde\Foundation\HydeKernel::class)]
class HydeHelperFacadeTest extends UnitTestCase
{
    protected static bool $needsKernel = true;
    protected static bool $needsConfig = true;

    public function testFeaturesFacadeReturnsInstanceOfFeaturesClass()
    {
        $this->assertInstanceOf(Features::class, Hyde::features());
    }

    public function testFeaturesFacadeCanBeUsedToCallStaticMethodsOnFeaturesClass()
    {
        $this->assertTrue(Hyde::features()->hasMarkdownPosts());
    }

    public function testHydeHasFeatureShorthandCallsStaticMethodOnFeaturesClass()
    {
        $this->assertTrue(Hyde::hasFeature(Feature::MarkdownPosts));
    }
}
