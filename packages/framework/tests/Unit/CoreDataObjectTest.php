<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit;

use Hyde\Framework\Factories\Concerns\CoreDataObject;
use Hyde\Pages\InMemoryPage;
use Hyde\Pages\MarkdownPage;
use Hyde\Testing\TestCase;

/**
 * @covers \Hyde\Framework\Factories\Concerns\CoreDataObject
 */
class CoreDataObjectTest extends TestCase
{
    public function testCoreDataObjectWithHydePage()
    {
        $this->assertInstanceOf(
            CoreDataObject::class,
            (new MarkdownPage(
            'foo',
        ))->toCoreDataObject());
    }

    public function testCoreDataObjectWithDynamicPage()
    {
        $this->assertInstanceOf(
            CoreDataObject::class,
            (new InMemoryPage(
                'foo',
            ))->toCoreDataObject()
        );
    }

    public function testToArray()
    {
        $this->assertSame([
            'pageClass' => MarkdownPage::class,
            'identifier' => 'foo',
            'sourcePath' => '_pages/foo.md',
            'outputPath' => 'foo.html',
            'routeKey' => 'foo',
        ], (new MarkdownPage(
            'foo',
        ))->toCoreDataObject()->toArray());
    }
}
