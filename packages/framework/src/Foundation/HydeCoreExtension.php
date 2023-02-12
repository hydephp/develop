<?php

declare(strict_types=1);

namespace Hyde\Foundation;

use Hyde\Foundation\Concerns\HydeExtension;
use Hyde\Pages\HtmlPage;
use Hyde\Pages\BladePage;
use Hyde\Pages\MarkdownPage;
use Hyde\Pages\MarkdownPost;
use Hyde\Pages\DocumentationPage;

/**
 * @see \Hyde\Framework\Testing\Feature\HydeCoreExtensionTest
 */
class HydeCoreExtension extends HydeExtension
{
    public static function getPageClasses(): array
    {
        return [
            HtmlPage::class,
            BladePage::class,
            MarkdownPage::class,
            MarkdownPost::class,
            DocumentationPage::class,
        ];
    }
}
