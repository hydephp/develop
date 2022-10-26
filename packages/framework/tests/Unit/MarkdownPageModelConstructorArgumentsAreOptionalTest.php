<?php
declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit;

use Hyde\Framework\Models\Pages\DocumentationPage;
use Hyde\Framework\Models\Pages\MarkdownPage;
use Hyde\Framework\Models\Pages\MarkdownPost;
use Hyde\Testing\TestCase;

class MarkdownPageModelConstructorArgumentsAreOptionalTest extends TestCase
{
    public function test_markdown_page_model_constructor_arguments_are_optional()
    {
        $models = [
            MarkdownPage::class,
            MarkdownPost::class,
            DocumentationPage::class,
        ];

        foreach ($models as $model) {
            $this->assertInstanceOf($model, new $model());
        }
    }
}
