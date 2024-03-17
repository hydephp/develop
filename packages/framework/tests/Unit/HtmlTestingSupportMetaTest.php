<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit;

use Hyde\Hyde;
use InvalidArgumentException;
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

    public function testTapElementUsingId()
    {
        $this->assertInstanceOf(TestableHtmlDocument::class,
            $this->html('<div id="foo">Foo</div>')->tapElement('#foo', fn (TestableHtmlElement $element) => $element->assertSee('Foo'))
        );
    }

    public function testGetElementUsingQuery()
    {
        $this->assertInstanceOf(TestableHtmlElement::class,
            $this->html($this->html)->getElementUsingQuery('head > title')->assertSee('Welcome to HydePHP!')
        );
    }

    public function testGetRootElement()
    {
        $element = $this->html('<div>Foo</div>')->getRootElement();

        $this->assertInstanceOf(TestableHtmlElement::class, $element);
        $this->assertSame('<div>Foo</div>', $element->html);
    }

    public function testGetElementById()
    {
        $this->assertInstanceOf(TestableHtmlElement::class,
            $this->html('<div id="foo">Foo</div>')->getElementById('foo')->assertSee('Foo')
        );

        $this->assertNull($this->html('<div id="foo">Foo</div>')->getElementById('bar'));
    }

    public function testElementUsingId()
    {
        $this->assertInstanceOf(TestableHtmlElement::class,
            $this->html('<div id="foo"><div class="bar">Baz</div></div>')->element('#foo')->assertSee('Baz')
        );

        $this->assertNull($this->html('<div id="foo"><div class="bar">Baz</div></div>')->element('#bar'));
    }

    public function testElementUsingSelector()
    {
        $this->assertInstanceOf(TestableHtmlElement::class,
            $this->html('<div><foo><bar>Baz</bar></foo></div>')->element('foo > bar')->assertSee('Baz')
        );

        $this->assertNull($this->html('<div><foo><bar>Baz</bar></foo></div>')->element('foo > baz'));
    }

    public function testElementUsingUnknownSyntax()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("The selector syntax 'foo' is not supported.");

        $this->html('<foo><bar>Baz</bar></foo>')->element('foo');
    }

    public function testGetElementsByClass()
    {
        $this->assertCount(1, $this->html('<div class="foo">Foo</div>')->getElementsByClass('foo'));
        $this->assertCount(0, $this->html('<div class="foo">Foo</div>')->getElementsByClass('bar'));
    }

    public function testGetElementsByClassWithChildNodes()
    {
        $html = <<<'HTML'
        <div class="foo">
            <div class="foo bar">Baz</div>
            <div class="foo bar">
                <div class="foo bar baz">Baz</div>
            </div>
        </div>
        HTML;

        $this->assertCount(4, $this->html($html)->getElementsByClass('foo'));
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
        $this->assertSame('bar', $this->html('<foo><bar>Baz</bar></foo>')->query('bar')->tag);

        $this->assertSame('foo', $this->html('<div><foo></div>')->query('foo')->tag);
        $this->assertSame('bar', $this->html('<div><foo><bar /></foo></div>')->query('foo > bar')->tag);
        $this->assertSame('bar', $this->html('<div><foo><bar></bar></foo></div>')->query('foo > bar')->tag);
        $this->assertSame('bar', $this->html('<div><foo><bar>Baz</bar></foo></div>')->query('foo > bar')->tag);
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

    public function testGetStructure()
    {
        $html = <<<'HTML'
        <main>
            <div>
                <h1>Foo</h1>
                <p>Bar <small>Baz</small></p>
            </div>
        </main>
        HTML;

        $expected = <<<'TXT'
        main
            div
                h1
                p
                    small
        TXT;

        $this->assertSame($expected, $this->html($html)->getStructure());
    }

    public function testGetTextRepresentation()
    {
        $html = <<<'HTML'
        <main>
            <div>
                <h1>Foo</h1>
                <p>Bar <small>Baz</small></p>
            </div>
        </main>
        HTML;

        $expected = <<<'TXT'
        Foo
        Bar Baz
        TXT;

        $this->assertSame($expected, $this->html($html)->getTextRepresentation());
    }

    public function testGetTextRepresentationWithMultipleLines()
    {
        $html = <<<'HTML'
        <main>
            <div>
                <h1>Foo</h1>
                <p>Bar <small>Baz</small></p>
            </div>
            <div>Line 2</div>
            <br>
            <div><p>Line 3</p></div>
        </main>
        HTML;

        $expected = <<<'TXT'
        Foo
        Bar Baz
        Line 2 
        Line 3
        Line 3
        TXT;

        $this->assertSame($expected, $this->html($html)->getTextRepresentation());
    }

    public function testAssertStructureLooksLike()
    {
        $html = <<<'HTML'
        <main>
            <div>
                <h1>Foo</h1>
                <p>Bar <small>Baz</small></p>
            </div>
        </main>
        HTML;

        $expected = <<<'TXT'
        main
            div
                h1
                p
                    small
        TXT;

        $this->html($html)->assertStructureLooksLike($expected);
    }

    public function testAssertLooksLike()
    {
        $html = <<<'HTML'
        <main>
            <div>
                <h1>Foo</h1>
                <p>Bar <small>Baz</small></p>
            </div>
        </main>
        HTML;

        $expected = <<<'TXT'
        Foo
        Bar Baz
        TXT;

        $this->html($html)->assertLooksLike($expected);
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

    public function testElementAttributes()
    {
        $this->assertSame([], $this->html('<div>Foo</div>')->getRootElement()->attributes);

        /** @noinspection HtmlUnknownAttribute */
        $this->assertSame([
            'name' => 'test',
            'foo' => 'bar',
            'href' => 'https://example.com/',
        ], $this->html('<div id="id" class="class" name="test" foo="bar" href="https://example.com/">Foo</div>')->getRootElement()->attributes);
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
        $this->assertSame(
            ['id' => 'foo', 'tag' => 'div', 'text' => 'Foo', 'classes' => ['bar']],
            $this->exampleElement()->toArray()
        );
    }

    public function testToArrayWithChildren()
    {
        $this->assertEquals(
            ['tag' => 'div', 'nodes' => collect([$this->html('<div><bar></bar></div>')->getRootElement()->nodes->first()])],
            $this->html('<div><bar></bar></div>')->getRootElement()->toArray()
        );

        $this->assertSame(
            ['id', 'tag', 'text', 'classes', 'attributes', 'nodes'],
            array_keys($this->html('<div id="id" class="class" name="name">Foo<bar></bar></div>')->getRootElement()->toArray())
        );
    }

    public function testToArrayWithAttributes()
    {
        /** @noinspection HtmlUnknownAttribute */
        $this->assertSame(
            ['id' => 'id', 'tag' => 'div', 'text' => 'Bar', 'classes' => ['class'], 'attributes' => ['name' => 'name']],
            $this->html('<div id="id" class="class" name="name">Bar</div>')->getRootElement()->toArray()
        );
    }

    public function testElementAssertHasClass()
    {
        $this->html('<div class="foo">Foo</div>')->getRootElement()->hasClass('foo');
    }

    public function testElementAssertDoesNotHaveClass()
    {
        $this->html('<div class="foo">Foo</div>')->getRootElement()->doesNotHaveClass('bar');
    }

    protected function exampleElement(): TestableHtmlElement
    {
        return $this->html('<div id="foo" class="bar">Foo</div>')->getElementById('foo');
    }
}
