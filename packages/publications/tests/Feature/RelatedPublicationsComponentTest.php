<?php

declare(strict_types=1);

namespace Hyde\Publications\Testing\Feature;

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
        // arrange
        $mockCurrentRoute = $this->createMock(Hyde::class);
        $mockCurrentRoute->method('getPage')->willReturn(new PublicationPage(['type' => null]));
        $this->mockHyde($mockCurrentRoute);

        $component = new RelatedPublicationsComponent();

        // act
        $result = $component->makeRelatedPublications();

        // assert
        $this->assertInstanceOf(Collection::class, $result);
        $this->assertTrue($result->isEmpty());
    }

    public function test_it_returns_empty_collection_if_publication_type_has_no_tag_fields()
    {
        // arrange
        $mockPublicationType = $this->createMock(PublicationType::class);
        $mockPublicationType->method('getFields')->willReturn(collect());
        $mockCurrentRoute = $this->createMock(Hyde::class);
        $mockCurrentRoute->method('getPage')->willReturn(new PublicationPage(['type' => $mockPublicationType]));
        $this->mockHyde($mockCurrentRoute);

        $component = new RelatedPublicationsComponent();

        // act
        $result = $component->makeRelatedPublications();

        // assert
        $this->assertInstanceOf(Collection::class, $result);
        $this->assertTrue($result->isEmpty());
    }

    public function test_it_returns_empty_collection_if_there_are_no_related_publications()
    {
        // arrange
        $mockPublicationType = $this->createMock(PublicationType::class);
        $mockPublicationType->method('getFields')->willReturn(collect(['tagGroup' => 'tag']));
        $mockPublicationService = $this->createMock(PublicationService::class);
        $mockPublicationService->method('getPublicationsForType')->willReturn(collect([
            new PublicationPage(['identifier' => '1', 'matter' => collect(['tag' => ['tag1']])])
        ]));
        $mockCurrentRoute = $this->createMock(Hyde::class);
        $mockCurrentRoute->method('getPage')->willReturn(new PublicationPage(['type' => $mockPublicationType, 'identifier' => '1']));
        $this->mockHyde($mockCurrentRoute);

        $component = new RelatedPublicationsComponent();

        // act
        $result = $component->makeRelatedPublications();

        // assert
        $this->assertInstanceOf(Collection::class, $result);
        $this->assertTrue($result->isEmpty());
    }
}
