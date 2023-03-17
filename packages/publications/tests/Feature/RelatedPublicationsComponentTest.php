<?php

declare(strict_types=1);

namespace Hyde\Publications\Testing\Feature;

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
}
