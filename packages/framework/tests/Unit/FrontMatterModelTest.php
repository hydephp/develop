<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit;

use Hyde\Markdown\Models\FrontMatter;
use Hyde\Testing\UnitTestCase;

/**
 * @covers \Hyde\Markdown\Models\FrontMatter
 */
class FrontMatterModelTest extends UnitTestCase
{
    public function testConstructorCreatesNewFrontMatterModel()
    {
        $matter = new FrontMatter([]);
        $this->assertInstanceOf(FrontMatter::class, $matter);
    }

    public function testConstructorArgumentsAreOptional()
    {
        $matter = new FrontMatter();
        $this->assertInstanceOf(FrontMatter::class, $matter);
    }

    public function testConstructorArgumentsAreAssigned()
    {
        $matter = new FrontMatter(['foo' => 'bar']);
        $this->assertEquals(['foo' => 'bar'], $matter->toArray());
    }

    public function testStaticFromArrayMethodCreatesNewFrontMatterModel()
    {
        $matter = FrontMatter::fromArray(['foo' => 'bar']);
        $this->assertInstanceOf(FrontMatter::class, $matter);
        $this->assertEquals(['foo' => 'bar'], $matter->toArray());
    }

    public function testToStringMagicMethodConvertsModelArrayIntoYamlFrontMatter()
    {
        $matter = new FrontMatter(['foo' => 'bar']);
        $this->assertEquals("---\nfoo: bar\n---\n", (string) $matter);
    }

    public function testMagicGetMethodReturnsFrontMatterProperty()
    {
        $matter = new FrontMatter(['foo' => 'bar']);
        $this->assertEquals('bar', $matter->foo);
    }

    public function testMagicGetMethodReturnsNullIfPropertyDoesNotExist()
    {
        $matter = new FrontMatter();
        $this->assertNull($matter->foo);
    }

    public function testGetMethodReturnsDataWhenNoArgumentIsSpecified()
    {
        $matter = new FrontMatter();
        $this->assertSame([], $matter->get());
    }

    public function testGetMethodReturnsDataWhenNoArgumentIsSpecifiedWithData()
    {
        $matter = new FrontMatter(['foo' => 'bar']);
        $this->assertSame(['foo' => 'bar'], $matter->get());
    }

    public function testGetMethodReturnsNullIfSpecifiedFrontMatterKeyDoesNotExist()
    {
        $matter = new FrontMatter();
        $this->assertNull($matter->get('bar'));
    }

    public function testGetMethodReturnsSpecifiedDefaultValueIfPropertyDoesNotExist()
    {
        $matter = new FrontMatter();
        $this->assertEquals('default', $matter->get('bar', 'default'));
    }

    public function testGetMethodReturnsSpecifiedFrontMatterValueIfKeyIsSpecified()
    {
        $matter = new FrontMatter(['foo' => 'bar']);
        $this->assertEquals('bar', $matter->get('foo'));
    }

    public function testSetMethodSetsFrontMatterProperty()
    {
        $matter = new FrontMatter();
        $matter->set('foo', 'bar');
        $this->assertEquals('bar', $matter->get('foo'));
    }

    public function testSetMethodReturnsSelf()
    {
        $matter = new FrontMatter();
        $this->assertSame($matter, $matter->set('foo', 'bar'));
    }

    public function testHasMethodReturnsTrueIfPropertyExists()
    {
        $matter = new FrontMatter(['foo' => 'bar']);
        $this->assertTrue($matter->has('foo'));
    }

    public function testHasMethodReturnsFalseIfPropertyDoesNotExist()
    {
        $matter = new FrontMatter();
        $this->assertFalse($matter->has('foo'));
    }

    public function testToArrayReturnsFrontMatterArray()
    {
        $matter = new FrontMatter(['foo' => 'bar']);
        $this->assertEquals(['foo' => 'bar'], $matter->toArray());
    }
}
