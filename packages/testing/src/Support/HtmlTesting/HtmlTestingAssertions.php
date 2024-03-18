<?php

declare(strict_types=1);

namespace Hyde\Testing\Support\HtmlTesting;

use Closure;
use Illuminate\Testing\Assert as PHPUnit;

trait HtmlTestingAssertions
{
    public function complete(): void
    {
        // Just an empty helper so we get easier Git diffs when adding new assertions.
    }

    public function assertSee(string $value): static
    {
        return $this->doAssert(fn () => PHPUnit::assertStringContainsString($value, $this->html, "The string '$value' was not found in the HTML."));
    }

    public function assertDontSee(string $value): static
    {
        return $this->doAssert(fn () => PHPUnit::assertStringNotContainsString($value, $this->html, "The string '$value' was found in the HTML."));
    }

    public function assertSeeEscaped(string $value): static
    {
        return $this->doAssert(fn () => PHPUnit::assertStringContainsString(e($value), $this->html, "The escaped string '$value' was not found in the HTML."));
    }

    public function assertDontSeeEscaped(string $value): static
    {
        return $this->doAssert(fn () => PHPUnit::assertStringNotContainsString(e($value), $this->html, "The escaped string '$value' was found in the HTML."));
    }

    public function assertHasId(string $id): TestableHtmlElement
    {
        return $this->doElementAssert(fn () => PHPUnit::assertSame($id, $this->id, 'The id attribute did not have the expected value.'));
    }

    public function assertDoesNotHaveId(string $id): TestableHtmlElement
    {
        return $this->doElementAssert(fn () => PHPUnit::assertNotSame($id, $this->id, 'The id attribute had the unexpected value.'));
    }

    public function assertHasClass(string $class): TestableHtmlElement
    {
        return $this->doElementAssert(fn () => PHPUnit::assertContains($class, $this->classes, "The class '$class' was not found in the element."));
    }

    public function assertDoesNotHaveClass(string $class): TestableHtmlElement
    {
        return $this->doElementAssert(fn () => PHPUnit::assertNotContains($class, $this->classes, "The class '$class' was found in the element."));
    }

    public function assertHasAttribute(string $attribute, ?string $value = null): TestableHtmlElement
    {
        if ($attribute === 'id') {
            return $this->assertHasId($value);
        }

        if ($attribute === 'class') {
            return $this->assertHasClass($value);
        }

        $this->doElementAssert(fn () => PHPUnit::assertArrayHasKey($attribute, $this->attributes, "The attribute '$attribute' was not found in the element."));

        if ($value) {
            return $this->doElementAssert(fn () => PHPUnit::assertSame($value, $this->attributes[$attribute], "The attribute '$attribute' did not have the expected value."));
        }

        return $this;
    }

    public function assertDoesNotHaveAttribute(string $attribute): TestableHtmlElement
    {
        return $this->doElementAssert(fn () => PHPUnit::assertArrayNotHasKey($attribute, $this->attributes, "The attribute '$attribute' was found in the element."));
    }

    /** @internal */
    public function doAssert(Closure $assertion): static
    {
        $assertion();

        return $this;
    }

    protected function doElementAssert(Closure $assertion): TestableHtmlElement
    {
        // Proxy to the root element if we're a TestableHtmlDocument.
        if ($this instanceof TestableHtmlDocument) {
            $rootElement = $this->getRootElement();

            // Bind closure to the root element.
            $assertion = $assertion->bindTo($rootElement);

            return $rootElement->doAssert($assertion);
        }

        return $this->doAssert($assertion);
    }
}
