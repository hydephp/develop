<?php

declare(strict_types=1);

namespace Hyde\Publications\Testing\Feature;

use Hyde\Support\Facades\Render;
use Hyde\Support\Models\Route;
use Hyde\Testing\TestCase;

use Hyde\Hyde;
use Hyde\Publications\Models\PublicationPage;
use Hyde\Publications\Models\PublicationType;
use Hyde\Publications\PublicationService;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Hyde\Publications\Views\Components\RelatedPublicationsComponent;

/**
 * @covers \Hyde\Publications\Views\Components\RelatedPublicationsComponent
 */
class RelatedPublicationsComponentTest extends TestCase
{
    public function test_it_returns_empty_collection_if_publication_type_is_not_set()
    {
        $this->mockRoute(new Route(new PublicationPage(type: new PublicationType('foo'))));

        $component = new RelatedPublicationsComponent();
        $result = $component->relatedPublications;

        $this->assertInstanceOf(Collection::class, $result);
        $this->assertTrue($result->isEmpty());
    }

    public function test_it_returns_empty_collection_if_publication_type_has_no_tag_fields()
    {
        $this->mockRoute(new Route(new PublicationPage(type: new PublicationType('foo'))));

        $component = new RelatedPublicationsComponent();
        $result = $component->relatedPublications;

        $this->assertInstanceOf(Collection::class, $result);
        $this->assertTrue($result->isEmpty());
    }

    public function test_it_returns_empty_collection_if_there_are_no_related_publications()
    {
        $mockPublicationType = new PublicationType('foo', fields: [['name' => 'foo', 'type' => 'tag']]);
        $this->mockRoute(new Route(new PublicationPage(type: $mockPublicationType)));

        $component = new RelatedPublicationsComponent();
        $result = $component->relatedPublications;

        $this->assertInstanceOf(Collection::class, $result);
        $this->assertTrue($result->isEmpty());
    }

    public function testRenderMethodReturnsView(): void
    {
        $this->mockRoute(new Route(new PublicationPage(type: new PublicationType('foo'))));

        $component = new RelatedPublicationsComponent();

        $this->assertInstanceOf(\Illuminate\Contracts\View\View::class, $component->render());
    }

    public function testMakeRelatedPublicationsReturnsCollection(): void
    {
        $this->mockRoute(new Route(new PublicationPage(type: new PublicationType('foo'))));
        $component = new RelatedPublicationsComponent();

        $this->assertInstanceOf(Collection::class, $component->relatedPublications);
    }

    public function testMakeRelatedPublicationsReturnsEmptyCollectionWhenNoPublicationType(): void
    {
        $this->mockRoute(new Route(new PublicationPage(type: new PublicationType('foo'))));
        $component = new RelatedPublicationsComponent();

        $this->assertEmpty($component->relatedPublications);
    }

    public function testMakeRelatedPublicationsReturnsEmptyCollectionWhenNoTagFields(): void
    {
        $this->mockRoute(new Route(new PublicationPage(type: new PublicationType('foo'))));
        $component = new RelatedPublicationsComponent();
        $component->relatedPublications;

        $this->assertEmpty($component->relatedPublications);
    }
}
