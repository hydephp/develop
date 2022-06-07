<?php

namespace Tests\Feature;

use Hyde\Framework\Helpers\Features;
use Hyde\Framework\Hyde;
use Tests\TestCase;

/**
 * @covers \Hyde\Framework\Helpers\HydeHelperFacade
 */
class HydeHelperFacadeTest extends TestCase
{
    // Test Hyde::features() facade returns an instance of Features::class
    public function testFeaturesFacadeReturnsInstanceOfFeaturesClass()
    {
        $this->assertInstanceOf(
            Features::class,
            Hyde::features()
        );
    }

    // Test Hyde::features() facade can be used to call static methods on Features::class
    public function testFeaturesFacadeCanBeUsedToCallStaticMethodsOnFeaturesClass()
    {
        $this->assertTrue(
            Hyde::features()->hasBlogPosts()
        );
    }

    // Test Hyde::hasFeature() shorthand calls the static method on Features::class
    public function testHydeHasFeatureShorthandCallsStaticMethodOnFeaturesClass()
    {
        $this->assertTrue(
            Hyde::hasFeature('blog-posts')
        );
    }
}
