<?php

declare(strict_types=1);

namespace Hyde\Publications\Testing\Feature;

use Hyde\Hyde;
use Hyde\Publications\Models\PublicationType;
use Hyde\Publications\Pages\PublicationPage;
use Hyde\Publications\Publications;
use Hyde\Testing\TestCase;

/**
 * @covers \Hyde\Publications\Models\PublicationTags
 */
class PublicationTagsTest extends TestCase
{
    public function testCanGetTagsUsedInPublications()
    {
        $type = new PublicationType('test-publication', fields: [[
            'name' => 'tag',
            'type' => 'tag',
        ]]);

        $page = new PublicationPage(matter: [
            'tag' => ['foo', 'bar'],
        ], type: $type);

        Hyde::kernel()->pages()->addPage($page);

        $this->assertSame(['foo', 'bar'], Publications::getPublicationTags());
    }

    public function testMultipleOccurringTagsAreAggregatedUniquely()
    {
        $type = new PublicationType('test-publication', fields: [[
            'name' => 'tag',
            'type' => 'tag',
        ]]);

        Hyde::kernel()->pages()->addPage(new PublicationPage('1', [
            'tag' => ['foo', 'bar'],
        ], type: $type));

        Hyde::kernel()->pages()->addPage(new PublicationPage('2', [
            'tag' => ['foo', 'baz'],
        ], type: $type));

        $this->assertSame(['foo', 'bar', 'baz'], Publications::getPublicationTags());
    }

    public function testAllTagsMethodFindsBothArrayAndSingleTagValues()
    {
        $type = new PublicationType('test-publication', fields: [[
            'name' => 'tag',
            'type' => 'tag',
        ]]);

        Hyde::kernel()->pages()->addPage(new PublicationPage('1', [
            'tag' => 'foo',
        ], type: $type));

        Hyde::kernel()->pages()->addPage(new PublicationPage('2', [
            'tag' => ['bar', 'baz'],
        ], type: $type));

        $this->assertSame(['foo', 'bar', 'baz'], Publications::getPublicationTags());
    }

    public function testAllTagsMethodReturnsEmptyArrayWhenThereAreNoTagsUsed()
    {
        $this->assertSame([], Publications::getPublicationTags());
    }
}
