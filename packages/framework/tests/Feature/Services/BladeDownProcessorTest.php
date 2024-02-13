<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature\Services;

use Hyde\Markdown\Processing\BladeDownProcessor;
use Hyde\Testing\TestCase;

/**
 * Class BladeDownProcessorTest.
 *
 * @covers \Hyde\Markdown\Processing\BladeDownProcessor
 */
class BladeDownProcessorTest extends TestCase
{
    public function testItRendersBladeEchoSyntax()
    {
        $this->assertEquals('Hello World!', BladeDownProcessor::render('[Blade]: {{ "Hello World!" }}'));
    }

    public function testItRendersBladeWithinMultilineMarkdown()
    {
        $this->assertEquals(
            "Foo\nHello World!\nBar",

            BladeDownProcessor::render("Foo\n[Blade]: {{ 'Hello World!' }}\nBar")
        );
    }

    public function testItRendersBladeViews()
    {
        if (! file_exists(resource_path('views'))) {
            mkdir(resource_path('views'));
        }

        file_put_contents(resource_path(
            'views/hello.blade.php'
        ), 'Hello World!');

        $this->assertEquals('Hello World!', BladeDownProcessor::render('[Blade]: @include("hello")'));

        unlink(resource_path('views/hello.blade.php'));
    }

    public function testDirectiveIsCaseInsensitive()
    {
        $this->assertEquals('Hello World!', BladeDownProcessor::render('[blade]: {{ "Hello World!" }}'));
    }

    public function testDirectiveIsIgnoredIfItIsNotAtTheStartOfALine()
    {
        $this->assertEquals('Example: [Blade]: {{ "Hello World!" }}',
            BladeDownProcessor::render('Example: [Blade]: {{ "Hello World!" }}'));
    }

    public function testItRendersBladeEchoSyntaxWithVariables()
    {
        $this->assertEquals('Hello World!', BladeDownProcessor::render('[Blade]: {{ $foo }}', ['foo' => 'Hello World!']));
    }

    public function testItRendersBladeViewsWithVariables()
    {
        file_put_contents(resource_path(
            'views/hello.blade.php'
        ), 'Hello {{ $name }}!');

        $this->assertEquals('Hello John!', BladeDownProcessor::render('[Blade]: @include("hello", ["name" => "John"])'));

        unlink(resource_path('views/hello.blade.php'));
    }

    public function testPreprocessMethodExpandsShortcode()
    {
        $this->assertEquals('<!-- HYDE[Blade]: {{ $foo }} -->', BladeDownProcessor::preprocess('[Blade]: {{ $foo }}'));
    }

    public function testProcessMethodRendersShortcode()
    {
        $this->assertEquals('Hello World!', BladeDownProcessor::postprocess('<!-- HYDE[Blade]: {{ $foo }} -->', ['foo' => 'Hello World!']));
    }
}
