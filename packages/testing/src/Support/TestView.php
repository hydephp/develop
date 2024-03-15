<?php

declare(strict_types=1);

namespace Hyde\Testing\Support;

use Hyde\Hyde;
use Illuminate\Support\Str;
use JetBrains\PhpStorm\NoReturn;
use Illuminate\Testing\Assert as PHPUnit;

class TestView extends \Illuminate\Testing\TestView
{
    /**
     * Assert that the given HTML is contained within the view.
     *
     * @return $this
     */
    public function assertSeeHtml(string $value, bool $ignoreFormatting = false): static
    {
        if ($ignoreFormatting) {
            return $this->assertSeeHtmlIgnoringFormatting($value);
        }

        return $this->assertSee($value, false);
    }

    /**
     * Assert that the given HTML is contained within the view text, ignoring whitespace and newlines.
     *
     * @return $this
     */
    public function assertSeeHtmlIgnoringFormatting(string $value): static
    {
        PHPUnit::assertStringContainsString($this->trimNewlinesAndIndentation($value), $this->trimNewlinesAndIndentation($this->rendered));

        return $this;
    }

    /**
     * Assert that the HTML attribute value is contained within the view.
     *
     * @return $this
     */
    public function assertAttributeIs(string $attribute, ?string $expectedValue = null): static
    {
        if ($expectedValue === null) {
            [$attributeName, $expectedValue] = explode('=', $attribute);
            $expectedValue = trim($expectedValue, '"');
        } else {
            $attributeName = $attribute;
        }

        static::assertHasAttribute($attributeName);

        PHPUnit::assertStringContainsString($attributeName.'="'.$expectedValue.'"', $this->rendered, "The attribute '$attributeName' with value '$expectedValue' was not found.");

        return $this;
    }

    /**
     * Assert that the HTML attribute is present within the view.
     *
     * @return $this
     */
    public function assertHasAttribute(string $attributeName): static
    {
        PHPUnit::assertStringContainsString($attributeName.'="', $this->rendered, "The attribute '$attributeName' was not found.");

        return $this;
    }

    /**
     * Assert that the HTML attribute is not present within the view.
     *
     * @return $this
     */
    public function assertDoesNotHaveAttribute(string $attributeName): static
    {
        PHPUnit::assertStringNotContainsString($attributeName.'="', $this->rendered, "The attribute '$attributeName' was found.");

        return $this;
    }

    /**
     * Assert that the given CSS class is contained within the view.
     *
     * @return $this
     */
    public function assertHasClass(string $class): static
    {
        PHPUnit::assertContains($class, $this->findClasses(), "The class '$class' was not found.");

        return $this;
    }

    /**
     * Assert that the given CSS class is not contained within the view.
     *
     * @return $this
     */
    public function assertDoesNotHaveClass(string $class): static
    {
        PHPUnit::assertNotContains($class, $this->findClasses(), "The class '$class' was found.");

        return $this;
    }

    /**
     * Assert that the given text is equals the view's text content.
     *
     * @return $this
     */
    public function assertTextIs(string $value): static
    {
        PHPUnit::assertSame($value, strip_tags($this->rendered));

        return $this;
    }

    #[NoReturn]
    public function dd(bool $writeHtml = true): void
    {
        if ($writeHtml) {
            $viewName = Str::after(Str::after(basename(class_basename($this->view->getName())), '.'), '.');
            file_put_contents(Hyde::path(Str::kebab($viewName.'.html')), $this->rendered);
        }

        exit(trim($this->rendered)."\n\n");
    }

    protected function trimNewlinesAndIndentation(string $value): string
    {
        return str_replace(['    ', "\t", "\n", "\r"], '', $value);
    }

    /** @return array<string> */
    protected function findClasses(): array
    {
        preg_match_all('/class="([^"]+)"/', $this->rendered, $matches);

        return explode(' ', $matches[1][0]);
    }
}
