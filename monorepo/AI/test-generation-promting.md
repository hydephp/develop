# AI test generation prompting

## General/unit

Todo

## View

````markdown
Please follow these instructions when generating view tests:

- This is for a Laravel-based static site generator called HydePHP. The tests are written in PHPUnit, but we have some custom helpers, listed below.
- When writing tests, use PHPUnit where tests are defined using this format: `public function testDoingSomethingDoesSomething()`, we do not specify return types on test methods, but we do when adding testing helper methods.
- Remember to cover all code paths for various configurations and test fixture setups,

Here is a base view testing setup:

```php
<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature;

use Hyde\Hyde;
use Hyde\Testing\TestCase;
use Hyde\Testing\TestsBladeViews;

/**
 * @coversNothing Example view test to ensure something does something.
 */
class ExampleViewTest extends TestCase
{
    use TestsBladeViews;

    public function testExampleView()
    {
        $view = $this->view(view('hyde::pages.example'));

        $view->assertSee('HydePHP')
            ->assertSee('Hello World!');
    }
}
```

The $this->view method signature looks like this: `protected function view(string|View $view, $data = []): TestView`
And the TestView class has the following methods:
```php
/** Assert that the given HTML is contained within the view. */
public function assertSeeHtml(string $value, bool $ignoreFormatting = false): $this;
/** Assert that the given HTML is contained within the view text, ignoring whitespace and newlines. */
public function assertSeeHtmlIgnoringFormatting(string $value): $this;
/** Assert that the given string is contained exactly `$times` within the view. */
public function assertSeeTimes(string $value, int $times = 1): $this;
/** Assert that the given string is contained exactly once within the view. */
public function assertSeeOnce(string $value): $this;
/** Assert that the given HTML element is contained within the view. */
public function assertHasElement(string $element): $this;
/** Assert that the HTML attribute value is contained within the view. */
public function assertAttributeIs(string $attribute, ?string $expectedValue = null): $this;
/** Assert that the HTML attribute is present within the view. */
public function assertHasAttribute(string $attributeName): $this;
/** Assert that the HTML attribute is not present within the view. */
public function assertDoesNotHaveAttribute(string $attributeName): $this;
/** Assert that the given HTML ID is contained within the view. */
public function assertHasId(string $id): $this;
/** Assert that the given CSS class is contained within the view. */
public function assertHasClass(string $class): $this;
/** Assert that the given CSS class is not contained within the view. */
public function assertDoesNotHaveClass(string $class): $this;
/** Assert that the given text is equals the view's text content. */
public function assertTextIs(string $value): $this;
/** Get the rendered view as a string. */
public function getRendered(): string;
```

When modifying the test setups, you may need to use any of the following helpers:

```php
// To create page instances, use this constructor signature as a reference:
public function __construct(string $identifier = '', array $matter = [], string $markdown = '') // Note that BladePages do not have the markdown parameter
// This applies for all page types: HtmlPage, BladePage, MarkdownPage, MarkdownPost, DocumentationPage (All under the Hyde\Pages namespace)

// Add a page into the site service container
\Hyde\Hyde::pages()->addPage($page);

// Mock the page being currently rendered
\Hyde\Hyde::shareViewData($page);

// set config data
config(['namespace.options' => ['key' => 'value']]);
```

````
