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
        $this->assertInstanceOf(FrontMatter::class, new FrontMatter([]));
    }

    public function testConstructorArgumentsAreOptional()
    {
        $this->assertInstanceOf(FrontMatter::class, new FrontMatter());
    }

    public function testConstructorArgumentsAreAssigned()
    {
        $this->assertEquals(['foo' => 'bar'], (new FrontMatter(['foo' => 'bar']))->toArray());
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
        $this->assertEquals("---\nfoo: bar\n---\n", (string) (new FrontMatter(['foo' => 'bar'])));
    }

    public function testMagicGetMethodReturnsFrontMatterProperty()
    {
        $this->assertEquals('bar', (new FrontMatter(['foo' => 'bar']))->foo);
    }

    public function testMagicGetMethodReturnsNullIfPropertyDoesNotExist()
    {
        $this->assertNull((new FrontMatter())->foo);
    }

    public function testGetMethodReturnsDataWhenNoArgumentIsSpecified()
    {
        $this->assertSame([], (new FrontMatter())->get());
    }

    public function testGetMethodReturnsDataWhenNoArgumentIsSpecifiedWithData()
    {
        $this->assertSame(['foo' => 'bar'], (new FrontMatter(['foo' => 'bar']))->get());
    }

    public function testGetMethodReturnsNullIfSpecifiedFrontMatterKeyDoesNotExist()
    {
        $this->assertNull((new FrontMatter())->get('bar'));
    }

    public function testGetMethodReturnsSpecifiedDefaultValueIfPropertyDoesNotExist()
    {
        $matter = new FrontMatter();
        $this->assertEquals('default', $matter->get('bar', 'default'));
    }

    public function testGetMethodReturnsSpecifiedFrontMatterValueIfKeyIsSpecified()
    {
        $this->assertEquals('bar', (new FrontMatter(['foo' => 'bar']))->get('foo'));
    }

    public function testSetMethodSetsFrontMatterProperty()
    {
        $this->assertEquals('bar', (new FrontMatter())->set('foo', 'bar')->get('foo'));
    }

    public function testSetMethodReturnsSelf()
    {
        $matter = new FrontMatter();
        $this->assertSame($matter, $matter->set('foo', 'bar'));
    }

    public function testHasMethodReturnsTrueIfPropertyExists()
    {
        $this->assertTrue((new FrontMatter(['foo' => 'bar']))->has('foo'));
    }

    public function testHasMethodReturnsFalseIfPropertyDoesNotExist()
    {
        $this->assertFalse((new FrontMatter())->has('foo'));
    }

    public function testToArrayReturnsFrontMatterArray()
    {
        $this->assertEquals(['foo' => 'bar'], (new FrontMatter(['foo' => 'bar']))->toArray());
    }
}
