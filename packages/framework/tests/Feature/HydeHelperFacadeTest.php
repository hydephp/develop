<?php

namespace Hyde\Testing\Framework\Feature;

use Hyde\Framework\Helpers\Features;
use Hyde\Framework\Hyde;
use Hyde\Testing\TestCase;

/**
 * @covers \Hyde\Framework\Helpers\HydeHelperFacade
 */
class HydeHelperFacadeTest extends TestCase
{
    public function testFeaturesFacadeReturnsInstanceOfFeaturesClass()
    {
        $this->assertInstanceOf(
            Features::class,
            Hyde::features()
        );
    }

    public function testFeaturesFacadeCanBeUsedToCallStaticMethodsOnFeaturesClass()
    {
        $this->assertTrue(
            Hyde::features()->hasBlogPosts()
        );
    }

    public function testHydeHasFeatureShorthandCallsStaticMethodOnFeaturesClass()
    {
        $this->assertTrue(
            Hyde::hasFeature('blog-posts')
        );
    }
}
