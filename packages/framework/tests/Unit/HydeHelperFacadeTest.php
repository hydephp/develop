<?php
declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit;

use Hyde\Framework\Helpers\Features;
use Hyde\Framework\Hyde;
use Hyde\Testing\TestCase;

/**
 * @covers \Hyde\Framework\HydeKernel
 */
class HydeHelperFacadeTest extends TestCase
{
    public function test_features_facade_returns_instance_of_features_class()
    {
        $this->assertInstanceOf(
            Features::class,
            Hyde::features()
        );
    }

    public function test_features_facade_can_be_used_to_call_static_methods_on_features_class()
    {
        $this->assertTrue(
            Hyde::features()->hasMarkdownPosts()
        );
    }

    public function test_hyde_has_feature_shorthand_calls_static_method_on_features_class()
    {
        $this->assertTrue(
            Hyde::hasFeature('markdown-posts')
        );
    }
}
