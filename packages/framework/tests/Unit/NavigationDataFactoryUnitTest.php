<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit;

use Hyde\Pages\MarkdownPage;
use Hyde\Testing\UnitTestCase;
use Hyde\Pages\DocumentationPage;
use Hyde\Markdown\Models\Markdown;
use Hyde\Markdown\Models\FrontMatter;
use Hyde\Framework\Factories\NavigationDataFactory;
use Hyde\Framework\Factories\Concerns\CoreDataObject;

/**
 * @covers \Hyde\Framework\Factories\NavigationDataFactory
 */
class NavigationDataFactoryUnitTest extends UnitTestCase
{
    protected function setUp(): void
    {
        self::needsKernel();
        self::mockConfig();
    }

    public function testSearchForPriorityInNavigationConfigForMarkdownPageWithKeyedConfig()
    {
        self::mockConfig(['hyde.navigation.order' => [
            'foo' => 15
        ]]);

        $factory = new NavigationConfigTestClass(new CoreDataObject(new FrontMatter(), new Markdown(), MarkdownPage::class, '', '', '', 'foo'), '');

        $this->assertSame(15, $factory->makePriority());
    }

    public function testSearchForPriorityInNavigationConfigForDocumentationPageWithList()
    {
        self::mockConfig(['docs.sidebar_order' => [
            'foo',
            'bar',
        ]]);

        $factory = new NavigationConfigTestClass(new CoreDataObject(new FrontMatter(), new Markdown(), DocumentationPage::class, 'foo', '', '', ''), '');
        $this->assertSame(500, $factory->makePriority());

        $factory = new NavigationConfigTestClass(new CoreDataObject(new FrontMatter(), new Markdown(), DocumentationPage::class, 'bar', '', '', ''), '');
        $this->assertSame(501, $factory->makePriority());
    }
}

class NavigationConfigTestClass extends NavigationDataFactory
{
    public function makePriority(): int
    {
        return parent::makePriority();
    }
}
