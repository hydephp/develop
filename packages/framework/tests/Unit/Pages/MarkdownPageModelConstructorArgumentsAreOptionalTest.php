<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit\Pages;

use Hyde\Pages\DocumentationPage;
use Hyde\Pages\MarkdownPage;
use Hyde\Pages\MarkdownPost;
use Hyde\Testing\TestCase;

class MarkdownPageModelConstructorArgumentsAreOptionalTest extends TestCase
{
    public function testMarkdownPageModelConstructorArgumentsAreOptional()
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
