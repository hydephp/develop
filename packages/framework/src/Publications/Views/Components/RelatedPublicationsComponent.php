<?php

declare(strict_types=1);

namespace Hyde\Publications\Views\Components;

use Hyde\Hyde;
use Hyde\Pages\Concerns\HydePage;
use Hyde\Publications\Models\PublicationPage;
use Hyde\Publications\Models\PublicationType;
use Hyde\Publications\PublicationService;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\View\Component;

class RelatedPublicationsComponent extends Component
{
    public Collection $relatedPublications;

    public function __construct()
    {
        $this->relatedPublications = $this->makeRelatedPublications();
    }

    /** @interitDoc */
    public function render(): Factory|View
    {
        return view('hyde-publications::components.related-publications');
    }

    protected function makeRelatedPublications(int $max = 5): Collection
    {
        // Get current publicationType
        $currHydePage = Hyde::currentRoute()->getPage();
        $publicationType = $currHydePage->getType();
        if (! $publicationType) {
            return collect();
        }

        // Get the tag fields for the current publicationType -> exit if there aren't any
        $tagFields = $publicationType->getFields()->filter(function ($field) {
            return $field->tagGroup !== null;
        });
        if ($tagFields->isEmpty()) {
            return collect();
        }

        // Get a list of all pages for this page's publicationType: 1 means we only have current page & no related pages exist
        $publicationPages = PublicationService::getPublicationsForType($publicationType)->keyBy('identifier');
        if ($publicationPages->count() <= 1) {
            return collect();
        }

        // Get all tags for the current page
        $currPageTags = $this->getTagsForPage($publicationPages->get($currHydePage->getIdentifier()), $tagFields);
        if ($currPageTags->isEmpty()) {
            return collect();
        }

        // Forget the current page pages since we don't want to show it as a related page against itself
        $publicationPages->forget($currHydePage->getIdentifier());

        // Get all related pages
        $allRelatedPages = $this->getAllRelatedPages($publicationPages, $tagFields, $currPageTags);
        if ($allRelatedPages->isEmpty()) {
            return collect();
        }

        // Sort them by relevance (count of shared tags & newest dates)
        return $this->sortRelatedPagesByRelevance($allRelatedPages, $max);
    }


    protected function getTagsForPage(PublicationPage $publicationPage, Collection $tagFields): Collection
    {
        $currPageTags = collect();

        // There could be multiple tag fields, most pubTypes will only have one
        foreach ($tagFields as $tagField) {
            $currPageTags = $currPageTags->merge($publicationPage->matter->get($tagField->name, []));
        }

        return $currPageTags;
    }


    protected function getAllRelatedPages (Collection $publicationPages, Collection $tagFields, Collection $currPageTags): Collection
    {
        $allRelatedPages = collect();

        foreach ($publicationPages as $publicationPage) {
            $pubPageTags     = $this->getTagsForPage($publicationPage, $tagFields);
            $matchedTagCount = $pubPageTags->intersect($currPageTags)->count();

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

        // Group related pages by the number of shared tags and then sort by keys (# of shared tags) descending
        $allRelatedPagesGrouped = $allRelatedPages->groupBy('count')->sortKeysDesc(SORT_NUMERIC);

        // Iterate over groups
        foreach ($allRelatedPagesGrouped as $v) {
            // Sort group by recency, newest pages first
            $sortedGroup = $v->sortByDesc('page.matter.__createdAt');

            // Now add to $relatedPages, quit at $max
            foreach ($sortedGroup as $vv) {
                $relatedPages->put($vv['identifier'], $vv['page']);
                if (count($relatedPages) >= $max) {
                    break 2;
                }
            }
        }

        return $relatedPages;
    }
}
