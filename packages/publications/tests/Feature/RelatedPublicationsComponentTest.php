<?php

declare(strict_types=1);

namespace Hyde\Publications\Testing\Feature;

use Carbon\Carbon;
use Hyde\Hyde;
use Hyde\Publications\Models\PublicationPage;
use Hyde\Publications\Models\PublicationType;
use Hyde\Publications\Views\Components\RelatedPublicationsComponent;
use Hyde\Support\Models\Route;
use Hyde\Testing\TestCase;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\View;
use Illuminate\View\ComponentAttributeBag;

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

    public function testTheRenderMethod()
    {
        $type = new PublicationType('foo', fields: [['name' => 'foo', 'type' => 'tag', 'tagGroup' => 'foo']]);
        $page = new PublicationPage('foo', ['foo' => 'bar'], type: $type);
        $this->mockRoute(new Route($page));

        $time = time();
        $page1 = new PublicationPage('page-1', ['foo' => 'bar', '__createdAt' => $time], type: $type);
        $page2 = new PublicationPage('page-2', ['foo' => 'bar', '__createdAt' => $time], type: $type);
        $page3 = new PublicationPage('page-3', ['foo' => 'bar', '__createdAt' => $time], type: $type);
        $page4 = new PublicationPage('page-4', ['foo' => 'bar', '__createdAt' => $time], type: $type);
        $page5 = new PublicationPage('page-5', ['foo' => 'bar', '__createdAt' => $time], type: $type);
        Hyde::pages()->addPage($page);
        Hyde::pages()->addPage($page1);
        Hyde::pages()->addPage($page2);
        Hyde::pages()->addPage($page3);
        Hyde::pages()->addPage($page4);
        Hyde::pages()->addPage($page5);

        $component = new RelatedPublicationsComponent(limit: 3);
        View::share('relatedPublications', $component->relatedPublications);
        View::share('title', $component->title);
        View::share('attributes', new ComponentAttributeBag());
        $dateFull = Carbon::parse($time);
        $dateShort = date('Y-m-d', $time);
        $this->assertEqualsIgnoringIndentation(<<<HTML
            <section class="prose dark:prose-invert">
                <h2>Related Publications</h2>
                <nav aria-label="related">
                    <ul>
                        <li>
                            <a href="{$page1->getRoute()}">Page 1</a>
                            <time datetime="$dateFull">($dateShort)</time>
                        </li>
                        <li>
                            <a href="{$page2->getRoute()}">Page 2</a>
                            <time datetime="$dateFull">($dateShort)</time>
                        </li>
                        <li>
                            <a href="{$page3->getRoute()}">Page 3</a>
                            <time datetime="$dateFull">($dateShort)</time>
                        </li>
                    </ul>
                </nav>
            </section>
        HTML, $component->render()->render());
    }

    protected function assertEqualsIgnoringIndentation(string $param, string $render)
    {
        $this->assertEquals(
            preg_replace('/\s+/', '', $param),
            preg_replace('/\s+/', '', $render)
        );
    }
}
