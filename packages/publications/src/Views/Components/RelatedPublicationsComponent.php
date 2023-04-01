<?php

declare(strict_types=1);

namespace Hyde\Publications\Views\Components;

use Hyde\Hyde;
use Hyde\Publications\Models\PublicationFieldDefinition;
use Hyde\Publications\Models\PublicationPage;
use Hyde\Publications\Publications;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\View\Component;

use function collect;
use function count;
use function view;

class RelatedPublicationsComponent extends Component
{
    /** @var Collection<string, PublicationPage> */
    public Collection $relatedPublications;

    public string $title = 'Related Publications';

    public function __construct(string $title = 'Related Publications', int $limit = 5)
    {
        $this->relatedPublications = $this->makeRelatedPublications($limit);
        $this->title = $title;
    }

    /** @interitDoc */
    public function render(): View
    {
        return view('hyde-publications::components.related-publications');
    }

    protected function makeRelatedPublications(int $limit = 5): Collection
    {
        // Get current publicationType from the current page
        $currentHydePage = Hyde::currentRoute()->getPage();

        // If not a publication page, exit early
        if (! $currentHydePage instanceof PublicationPage) {
            return collect();
        }

        $publicationType = $currentHydePage->getType();

        // Get the tag fields for the current publicationType or exit early if there aren't any
        $publicationTypeTagFields = $publicationType->getFields()->filter(function (PublicationFieldDefinition $field): bool {
            return $field->tagGroup !== null;
        });
        if ($publicationTypeTagFields->isEmpty()) {
            return collect();
        }

        // Get a list of all pages for this page's publicationType: 1 means we only have current page & no related pages exist
        $publicationPages = Publications::getPublicationsForType($publicationType)->keyBy('identifier');
        if ($publicationPages->count() <= 1) {
            return collect();
        }

        // Get all tags for the current page
        $currentPageTags = $this->getTagsForPage($publicationPages->get($currentHydePage->getIdentifier()), $publicationTypeTagFields);
        if ($currentPageTags->isEmpty()) {
            return collect();
        }

        // Forget the current page pages since we don't want to show it as a related page against itself
        $publicationPages->forget($currentHydePage->getIdentifier());

        // Get all related pages
        $allRelatedPages = $this->getAllRelatedPages($publicationPages, $publicationTypeTagFields, $currentPageTags);
        if ($allRelatedPages->isEmpty()) {
            return collect();
        }

        // Sort them by relevance (count of shared tags & newest dates)
        return $this->sortRelatedPagesByRelevance($allRelatedPages, $limit);
    }

    protected function getTagsForPage(PublicationPage $publicationPage, Collection $tagFields): Collection
    {
        $thisPageTags = collect();

        // There could be multiple tag fields, but most publication types will only have one
        foreach ($tagFields as $tagField) {
            $thisPageTags = $thisPageTags->merge($publicationPage->matter->get($tagField->name, []));
        }

        return $thisPageTags;
    }

    protected function getAllRelatedPages(Collection $publicationPages, Collection $tagFields, Collection $currPageTags): Collection
    {
        $allRelatedPages = collect();

        foreach ($publicationPages as $publicationPage) {
            $publicationPageTags = $this->getTagsForPage($publicationPage, $tagFields);
            $matchedTagCount = $publicationPageTags->intersect($currPageTags)->count();

            // We have shared/matching tags, add this page info to $allRelatedPages
            if ($matchedTagCount) {
                $allRelatedPages->add(
                    collect([
                        'count'      => $matchedTagCount,
                        'identifier' => $publicationPage->identifier,
                        'page'       => $publicationPage,
                    ])
                );
            }
        }

        return $allRelatedPages;
    }

    protected function sortRelatedPagesByRelevance(Collection $allRelatedPages, int $max): Collection
    {
        $relatedPages = collect();

        // Group related pages by the number of shared tags and then sort by keys (number of shared tags) descending
        $allRelatedPagesGrouped = $allRelatedPages->groupBy('count')->sortKeysDesc(SORT_NUMERIC);

        // Iterate over groups
        foreach ($allRelatedPagesGrouped as $relatedPagesGroup) {
            // Sort group by recency, with the latest pages first
            $sortedPageGroup = $relatedPagesGroup->sortByDesc('page.matter.__createdAt');

            // Now add to $relatedPages, and stop when hitting $max
            foreach ($sortedPageGroup as $page) {
                $relatedPages->put($page['identifier'], $page['page']);
                if (count($relatedPages) >= $max) {
                    break 2;
                }
            }
        }

        return $relatedPages;
    }
}
