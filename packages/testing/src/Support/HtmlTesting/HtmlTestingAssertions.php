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

    public function hasId(string $id): static
    {
        return $this->doElementAssert(fn () => PHPUnit::assertSame($id, $this->id, 'The id attribute did not have the expected value.'));
    }

    public function doesNotHaveId(string $id): static
    {
        return $this->doElementAssert(fn () => PHPUnit::assertNotSame($id, $this->id, 'The id attribute had the unexpected value.'));
    }

    public function hasClass(string $class): static
    {
        return $this->doElementAssert(fn () => PHPUnit::assertContains($class, $this->classes, "The class '$class' was not found in the element."));
    }

    public function doesNotHaveClass(string $class): static
    {
        return $this->doElementAssert(fn () => PHPUnit::assertNotContains($class, $this->classes, "The class '$class' was found in the element."));
    }

    public function hasAttribute(string $attribute, ?string $value = null): static
    {
        if ($attribute === 'id') {
            return $this->hasId($value);
        }

        if ($attribute === 'class') {
            return $this->hasClass($value);
        }

        $this->doElementAssert(fn () => PHPUnit::assertArrayHasKey($attribute, $this->attributes, "The attribute '$attribute' was not found in the element."));

        if ($value) {
            return $this->doElementAssert(fn () => PHPUnit::assertSame($value, $this->attributes[$attribute], "The attribute '$attribute' did not have the expected value."));
        }

        return $this;
    }

    public function doesNotHaveAttribute(string $attribute): static
    {
        return $this->doElementAssert(fn () => PHPUnit::assertArrayNotHasKey($attribute, $this->attributes, "The attribute '$attribute' was found in the element."));
    }

    /** @internal */
    public function doAssert(Closure $assertion): static
    {
        $assertion();

        return $this;
    }

    protected function doElementAssert(Closure $assertion): static
    {
        return $this->doAssert($assertion);
    }
}
