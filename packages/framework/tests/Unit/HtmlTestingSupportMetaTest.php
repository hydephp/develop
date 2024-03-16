<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit;

use Hyde\Hyde;
use Hyde\Testing\UnitTestCase;
use Hyde\Testing\TestsBladeViews;
use Hyde\Testing\Support\HtmlTesting\TestableHtmlElement;
use Hyde\Testing\Support\HtmlTesting\TestableHtmlDocument;

/**
 * Meta test for the HTML testing support.
 *
 * @see \Hyde\Testing\Support\TestView
 * @see \Hyde\Testing\Support\HtmlTesting
 *
 * @coversNothing
 */
class HtmlTestingSupportMetaTest extends UnitTestCase
{
    use TestsBladeViews;

    protected string $html;

    protected function setUp(): void
    {
        parent::setUp();

        self::needsKernel();

        $this->html ??= file_get_contents(Hyde::vendorPath('resources/views/homepages/welcome.blade.php'));
    }

    public function testHtmlHelper()
    {
        $this->assertInstanceOf(TestableHtmlDocument::class, $this->html($this->html));
    }

    public function testAssertSee()
    {
        $this->html($this->html)
            ->assertSee('<title>Welcome to HydePHP!</title>')
            ->assertDontSee('<title>Unwelcome to HydePHP!</title>');
    }

    public function testAssertSeeEscaped()
    {
        $this->html(e('<div>Foo</div>').'<div>Bar</div>')
            ->assertSeeEscaped('<div>Foo</div>')
            ->assertDontSeeEscaped('<div>Bar</div>')
            ->assertDontSee('<div>Foo</div>')
            ->assertSee('<div>Bar</div>');
    }

    public function testTapElement()
    {
        $this->assertInstanceOf(TestableHtmlDocument::class,
            $this->html($this->html)->tapElement('head > title', fn (TestableHtmlElement $element) => $element->assertSee('Welcome to HydePHP!'))
        );
    }

    public function testAssertElement()
    {
        $this->assertInstanceOf(TestableHtmlElement::class,
            $this->html($this->html)->element('head > title')->assertSee('Welcome to HydePHP!')
        );
    }

    public function testQuery()
    {
        $this->assertInstanceOf(TestableHtmlElement::class,
            $this->html($this->html)->query('head > title')->assertSee('Welcome to HydePHP!')
        );

        $this->assertNull($this->html($this->html)->query('head > title > h1'));
    }

    public function testQueryWithEdgeCases()
    {
        $this->assertSame('foo', $this->html('<foo>')->query('')->tag);
        $this->assertSame('bar', $this->html('<foo><bar /></foo>')->query('bar')->tag);
        $this->assertSame('bar', $this->html('<foo><bar></bar></foo>')->query('bar')->tag);
    }

    public function testGetElementById()
    {
        $this->assertInstanceOf(TestableHtmlElement::class,
            $this->html('<div id="foo">Foo</div>')->getElementById('foo')->assertSee('Foo')
        );

        $this->assertNull($this->html('<div id="foo">Foo</div>')->getElementById('bar'));
    }

    public function testGetRootElement()
    {
        $element = $this->html('<div>Foo</div>')->getRootElement();

        $this->assertInstanceOf(TestableHtmlElement::class, $element);
        $this->assertSame('<div>Foo</div>', $element->html);
    }

    public function testElementInstance()
    {
        $this->assertInstanceOf(TestableHtmlElement::class, $this->exampleElement());
    }

    public function testElementTag()
    {
        $this->assertSame('div', $this->exampleElement()->tag);
    }

    public function testElementText()
    {
        $this->assertSame('Foo', $this->exampleElement()->text);
    }

    public function testElementHtml()
    {
        $this->assertSame('<div id="foo" class="bar">Foo</div>', $this->exampleElement()->html);
    }

    public function testElementId()
    {
        $this->assertSame('foo', $this->exampleElement()->id);

        $this->assertNull($this->html('<div>Foo</div>')->getRootElement()->id);
    }

    public function testElementClasses()
    {
        $this->assertSame([], $this->html('<div>Foo</div>')->getRootElement()->classes);
        $this->assertSame(['foo', 'bar'], $this->html('<div class="foo bar">Foo</div>')->getRootElement()->classes);
    }

    public function testElementNodes()
    {
        $this->assertNull($this->exampleElement()->nodes->first());
    }

    public function testElementNodesWithChild()
    {
        $child = $this->html('<div><foo>Bar</foo></div>')->getRootElement()->nodes->first();

        $this->assertInstanceOf(TestableHtmlElement::class, $child);
        $this->assertSame('foo', $child->tag);
        $this->assertSame('Bar', $child->text);
    }

    public function testElementNodesWithChildren()
    {
        $element = $this->html('<div><foo>Bar</foo><bar>Baz<small>Foo</small></bar></div>')->getRootElement();

        $this->assertCount(2, $element->nodes);
        $this->assertSame('foo', $element->nodes->first()->tag);
        $this->assertSame('bar', $element->nodes->last()->tag);

        $this->assertCount(1, $element->nodes->last()->nodes);
        $this->assertSame('small', $element->nodes->last()->nodes->first()->tag);

        $this->assertSame('Foo', $element->nodes->last()->nodes->first()->text);
        $this->assertNull($element->nodes->last()->nodes->first()->nodes->first());
    }

    public function testElementToArray()
    {
        $array = $this->exampleElement()->toArray();

        $this->assertEquals(['tag' => 'div', 'text' => 'Foo', 'nodes' => collect(), 'id' => 'foo', 'classes' => ['bar']], $array);
        $this->assertSame(['tag', 'text', 'nodes', 'id', 'classes'], array_keys($array));
    }

    public function testToArrayWithChildren()
    {
        $element = $this->html('<div><bar></bar></div>')->getRootElement();
        $this->assertEquals(['tag' => 'div', 'nodes' => collect([$element->nodes->first()])], $element->toArray());
    }

    public function testElementAssertHasClass()
    {
        $this->html('<div class="foo">Foo</div>')->getRootElement()->hasClass('foo');
    }

    public function testElementAssertDoesNotHaveClass()
    {
        $this->html('<div class="foo">Foo</div>')->getRootElement()->doesNotHaveClass('bar');
    }

    public function testDumpHelper()
    {
        $dump = $this->html($this->html)->dump(false);

        $this->assertStringContainsString('Document Dump', $dump);
        $this->assertStringContainsString('Document Preview', $dump);
        $this->assertStringContainsString('Raw HTML', $dump);
        $this->assertStringContainsString('Nodes', $dump);

        $this->assertStringContainsString(e('<title>Welcome to HydePHP!</title>'), $dump);
    }

    protected function exampleElement(): TestableHtmlElement
    {
        return $this->html('<div id="foo" class="bar">Foo</div>')->getElementById('foo');
    }
}
