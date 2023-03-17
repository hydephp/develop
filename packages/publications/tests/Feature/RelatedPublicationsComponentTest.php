<?php

declare(strict_types=1);

namespace Hyde\Publications\Testing\Feature;

use Hyde\Hyde;
use Hyde\Publications\Models\PublicationPage;
use Hyde\Publications\Models\PublicationType;
use Hyde\Publications\Views\Components\RelatedPublicationsComponent;
use Hyde\Support\Models\Route;
use Hyde\Testing\TestCase;
use Illuminate\Support\Collection;

/**
 * @covers \Hyde\Publications\Views\Components\RelatedPublicationsComponent
 */
class RelatedPublicationsComponentTest extends TestCase
{
    public function testWithStandardPage()
    {
        $this->mockRoute();

        $component = new RelatedPublicationsComponent();
        $this->assertEquals(new Collection(), $component->relatedPublications);
    }

    public function testWithBlankPublicationType()
    {
        $type = new PublicationType('foo');
        $page = new PublicationPage('foo', type: $type);
        $this->mockRoute(new Route($page));

        $component = new RelatedPublicationsComponent();
        $this->assertEquals(new Collection(), $component->relatedPublications);
    }

    public function testWithTagFieldButNoTagGroup()
    {
        $type = new PublicationType('foo', fields: [['name' => 'foo', 'type' => 'tag']]);
        $page = new PublicationPage('foo', type: $type);
        $this->mockRoute(new Route($page));

        $component = new RelatedPublicationsComponent();
        $this->assertEquals(new Collection(), $component->relatedPublications);
    }

    public function testWithEmptyTagGroup()
    {
        $type = new PublicationType('foo', fields: [['name' => 'foo', 'type' => 'tag', 'tagGroup' => 'foo']]);
        $page = new PublicationPage('foo', type: $type);
        $this->mockRoute(new Route($page));

        $component = new RelatedPublicationsComponent();
        $this->assertEquals(new Collection(), $component->relatedPublications);
    }

    public function testWithPublicationWithTag()
    {
        $type = new PublicationType('foo', fields: [['name' => 'foo', 'type' => 'tag', 'tagGroup' => 'foo']]);
        $page = new PublicationPage('foo', ['foo' => 'bar'], type: $type);
        $this->mockRoute(new Route($page));

        $component = new RelatedPublicationsComponent();
        $this->assertEquals(new Collection(), $component->relatedPublications);
    }

    public function testWithMoreTaggedPublications()
    {
        $type = new PublicationType('foo', fields: [['name' => 'foo', 'type' => 'tag', 'tagGroup' => 'foo']]);
        $page = new PublicationPage('foo', ['foo' => 'bar'], type: $type);
        $this->mockRoute(new Route($page));

        $otherPage = new PublicationPage('bar', ['foo' => 'bar'], type: $type);
        Hyde::pages()->addPage($page);
        Hyde::pages()->addPage($otherPage);

        $component = new RelatedPublicationsComponent();
        $this->assertEquals(new Collection(['foo/bar' => $otherPage]), $component->relatedPublications);
    }

    public function testWithPublicationsWithOtherTagValue()
    {
        $type = new PublicationType('foo', fields: [['name' => 'foo', 'type' => 'tag', 'tagGroup' => 'foo']]);
        $page = new PublicationPage('foo', ['foo' => 'bar'], type: $type);
        $this->mockRoute(new Route($page));

        $otherPage = new PublicationPage('bar', ['foo' => 'baz'], type: $type);
        Hyde::pages()->addPage($page);
        Hyde::pages()->addPage($otherPage);

        $component = new RelatedPublicationsComponent();
        $this->assertEquals(new Collection(), $component->relatedPublications);
    }

    public function testWithPublicationsWithCurrentOneBeingUntagged()
    {
        $type = new PublicationType('foo', fields: [['name' => 'foo', 'type' => 'tag', 'tagGroup' => 'foo']]);
        $page = new PublicationPage('foo', type: $type);
        $this->mockRoute(new Route($page));

        $otherPage = new PublicationPage('bar', ['foo' => 'bar'], type: $type);
        Hyde::pages()->addPage($page);
        Hyde::pages()->addPage($otherPage);

        $component = new RelatedPublicationsComponent();
        $this->assertEquals(new Collection(), $component->relatedPublications);
    }

    public function testWithMultipleRelatedPages()
    {
        $type = new PublicationType('foo', fields: [['name' => 'foo', 'type' => 'tag', 'tagGroup' => 'foo']]);
        $page = new PublicationPage('foo', ['foo' => 'bar'], type: $type);
        $this->mockRoute(new Route($page));

        $page1 = new PublicationPage('page-1', ['foo' => 'bar'], type: $type);
        $page2 = new PublicationPage('page-2', ['foo' => 'bar'], type: $type);
        $page3 = new PublicationPage('page-3', ['foo' => 'bar'], type: $type);
        $page4 = new PublicationPage('page-4', ['foo' => 'bar'], type: $type);
        $page5 = new PublicationPage('page-5', ['foo' => 'bar'], type: $type);
        Hyde::pages()->addPage($page);
        Hyde::pages()->addPage($page1);
        Hyde::pages()->addPage($page2);
        Hyde::pages()->addPage($page3);
        Hyde::pages()->addPage($page4);
        Hyde::pages()->addPage($page5);

        $component = new RelatedPublicationsComponent();
        $this->assertEquals(new Collection([
            'foo/page-1' => $page1,
            'foo/page-2' => $page2,
            'foo/page-3' => $page3,
            'foo/page-4' => $page4,
            'foo/page-5' => $page5,
        ]), $component->relatedPublications);
    }

    public function testWithMultipleRelatedPagesAndLimit()
    {
        $type = new PublicationType('foo', fields: [['name' => 'foo', 'type' => 'tag', 'tagGroup' => 'foo']]);
        $page = new PublicationPage('foo', ['foo' => 'bar'], type: $type);
        $this->mockRoute(new Route($page));

        $page1 = new PublicationPage('page-1', ['foo' => 'bar'], type: $type);
        $page2 = new PublicationPage('page-2', ['foo' => 'bar'], type: $type);
        $page3 = new PublicationPage('page-3', ['foo' => 'bar'], type: $type);
        $page4 = new PublicationPage('page-4', ['foo' => 'bar'], type: $type);
        $page5 = new PublicationPage('page-5', ['foo' => 'bar'], type: $type);
        Hyde::pages()->addPage($page);
        Hyde::pages()->addPage($page1);
        Hyde::pages()->addPage($page2);
        Hyde::pages()->addPage($page3);
        Hyde::pages()->addPage($page4);
        Hyde::pages()->addPage($page5);

        $component = new RelatedPublicationsComponent(limit: 3);
        $this->assertEquals(new Collection([
            'foo/page-1' => $page1,
            'foo/page-2' => $page2,
            'foo/page-3' => $page3,
        ]), $component->relatedPublications);
    }
}
