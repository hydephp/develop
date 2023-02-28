<?php

declare(strict_types=1);

namespace Hyde\Framework\Views\Components;

use Hyde\Hyde;
use Hyde\Publications\PublicationService;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class RelatedPublicationsComponent extends Component
{
    public array $relatedPublications;

    public function __construct()
    {
        $this->relatedPublications = $this->makeRelatedPublications();
    }

    /** @interitDoc */
    public function render(): Factory|View
    {
        return view('hyde::components.relatedPublications');
    }

    protected function makeRelatedPublications(int $max=5): array
    {
        // Get current publicationType
        $currPage         = Hyde::currentRoute()->getPage();
        $publicationType  = $currPage->getType();
        if (!$publicationType) {
            return [];
        }

        // Get the tag fields for the current publicationType -> exit if there aren't any
        $tagFields = $publicationType->getFields()->filter(function($field) {
            return $field->tagGroup !== null;
        });
        if ($tagFields->isEmpty()) {
            return [];
        }

        // Get a list of all tags for the current page
        $publicationPages = PublicationService::getPublicationsForType($publicationType)->keyBy('identifier');
        $thisPage = $publicationPages->get($currPage->getIdentifier());
        $publicationPages->forget($currPage->getIdentifier());
        $thisPageTags = [];
        foreach ($tagFields as $tagField) {
            $thisPageTags = array_merge($thisPageTags, $thisPage->matter->get($tagField->name, []));
        }

        $allRelatedPages = [];
        foreach ($publicationPages as $publicationPage) {
            // Get a list of all tags for $publicationPage
            $pubPageTags = [];
            foreach ($tagFields as $tagField) {
                $pubPageTags = array_merge($pubPageTags, $publicationPage->matter->get($tagField->name, []));
            }

            // Now count how many of $thisPageTags are also in $pubPageTags
            $count = 0;
            foreach ($thisPageTags as $thisPageTag) {
                if (in_array($thisPageTag, $pubPageTags)) {
                    $count++;
                }
            }

            // We have shared/matching tags, add this page and it's count to $allRelatedPages
            if ($count) {
                $allRelatedPages[$publicationPage->identifier] = $count;
            }
        }

        // We found nothing -> exit
        if (!count($allRelatedPages)) {
            return [];
        }

        // Sort by count, preserve keys
        arsort($allRelatedPages, SORT_NUMERIC);

        // Now get the top $max pages (or less if there aren't that many)
        $relatedPages = [];
        $aks = array_keys($allRelatedPages);
        $lim = min(count($allRelatedPages), $max);
        for ($i=0; $i<$lim; $i++) {
            $relatedPages[] = $publicationPages->get($aks[$i]);
        }

        return $relatedPages;
    }
}
