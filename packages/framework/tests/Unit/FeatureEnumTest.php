<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit;

use Hyde\Testing\UnitTestCase;
use Hyde\Enums\Feature;

#[\PHPUnit\Framework\Attributes\CoversClass(\Hyde\Enums\Feature::class)]
class FeatureEnumTest extends UnitTestCase
{
    public function testEnumCases()
    {
        $this->assertSame([
            Feature::HtmlPages,
            Feature::MarkdownPosts,
            Feature::BladePages,
            Feature::MarkdownPages,
            Feature::DocumentationPages,
            Feature::Darkmode,
            Feature::DocumentationSearch,
            Feature::Torchlight,
        ], Feature::cases());
    }

    public function testFromNameMethod()
    {
        $this->assertSame(Feature::HtmlPages, Feature::fromName('HtmlPages'));
        $this->assertSame(Feature::MarkdownPosts, Feature::fromName('MarkdownPosts'));
        $this->assertSame(Feature::BladePages, Feature::fromName('BladePages'));
        $this->assertSame(Feature::MarkdownPages, Feature::fromName('MarkdownPages'));
        $this->assertSame(Feature::DocumentationPages, Feature::fromName('DocumentationPages'));
        $this->assertSame(Feature::Darkmode, Feature::fromName('Darkmode'));
        $this->assertSame(Feature::DocumentationSearch, Feature::fromName('DocumentationSearch'));
        $this->assertSame(Feature::Torchlight, Feature::fromName('Torchlight'));
    }

    public function testFromNameMethodReturnsNullForInvalidName()
    {
        $this->assertNull(Feature::fromName('InvalidName'));
    }
}
