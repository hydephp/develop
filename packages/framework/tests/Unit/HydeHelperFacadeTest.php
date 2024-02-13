<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit;

use Hyde\Facades\Features;
use Hyde\Hyde;
use Hyde\Testing\TestCase;

/**
 * @covers \Hyde\Foundation\HydeKernel
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
            Hyde::features()->hasMarkdownPosts()
        );
    }

    public function testHydeHasFeatureShorthandCallsStaticMethodOnFeaturesClass()
    {
        $this->assertTrue(
            Hyde::hasFeature('markdown-posts')
        );
    }
}
