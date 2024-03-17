<?php

declare(strict_types=1);

namespace Hyde\Testing\Support\HtmlTesting;

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

    /** @note Use this sparingly, as you generally should not care about the exact HTML structure. */
    public function assertStructureLooksLike($expected): static
    {
        // TODO
    }

    /** A better alternative to assertStructureLooksLike, as it only cares about the visible text. */
    public function assertLooksLike($expected): static
    {
        // TODO
    }

    protected function doAssert(callable $assertion): static
    {
        $assertion();

        return $this;
    }
}
