<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit;

use Hyde\Testing\UnitTestCase;
use Hyde\Enums\Feature;

/**
 * @covers \Hyde\Enums\Feature
 */
class FeatureEnumTest extends UnitTestCase
{
    public function testEnumCasesExist()
    {
        $this->assertInstanceOf(Feature::class, Feature::HtmlPages);
        $this->assertInstanceOf(Feature::class, Feature::MarkdownPosts);
        $this->assertInstanceOf(Feature::class, Feature::BladePages);
        $this->assertInstanceOf(Feature::class, Feature::MarkdownPages);
        $this->assertInstanceOf(Feature::class, Feature::DocumentationPages);
        $this->assertInstanceOf(Feature::class, Feature::Darkmode);
        $this->assertInstanceOf(Feature::class, Feature::DocumentationSearch);
        $this->assertInstanceOf(Feature::class, Feature::Torchlight);
    }

    public function testEnumValuesAreUnique()
    {
        $values = array_map(fn($case) => $case->name, Feature::cases());
        $uniqueValues = array_unique($values);
        $this->assertSame(count($values), count($uniqueValues));
    }
}
