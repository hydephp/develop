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
}
