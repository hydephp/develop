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

    public function testEnumValuesAreUnique()
    {
        $values = array_map(fn($case) => $case->name, Feature::cases());
        $uniqueValues = array_unique($values);
        $this->assertSame(count($values), count($uniqueValues));
    }
}
