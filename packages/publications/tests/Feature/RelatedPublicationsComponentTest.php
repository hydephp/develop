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
}
