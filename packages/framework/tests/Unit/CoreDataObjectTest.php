<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit;

use Hyde\Framework\Factories\Concerns\CoreDataObject;
use Hyde\Pages\MarkdownPage;
use Hyde\Testing\TestCase;

/**
 * @covers \Hyde\Framework\Factories\Concerns\CoreDataObject
 */
class CoreDataObjectTest extends TestCase
{
    public function testCoreDataObject()
    {
        $this->assertInstanceOf(
            CoreDataObject::class,
            (new MarkdownPage(
            'foo',
        ))->toCoreDataObject());
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
