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
        return view('hyde-publications::components.related-publications');
    }

    protected function makeRelatedPublications(int $max = 5): array
    {
        // Get current publicationType
        $currPage = Hyde::currentRoute()->getPage();
        $publicationType = $currPage->getType();
        if (! $publicationType) {
            return [];
        }

        // Get the tag fields for the current publicationType -> exit if there aren't any
        $tagFields = $publicationType->getFields()->filter(function ($field) {
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

        $allRelatedPages = collect();
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
                $allRelatedPages->add(
                    collect([
                        'count'      => $count,
                        'identifier' => $publicationPage->identifier,
                        'page'       => $publicationPage,
                    ])
                );
            }
        }

        // We found nothing -> exit
        if (! count($allRelatedPages)) {
            return [];
        }

        // Sort everything by count and then by most recent date -> seems the most logical & relevant
        $allRelatedPagesGrouped = $allRelatedPages->groupBy('count')->sortKeysDesc(SORT_NUMERIC);
        $relatedPages = [];
        foreach ($allRelatedPagesGrouped as $k=>$v) {
            $sorted = $v->sortByDesc('page.matter.__createdAt');
            foreach ($sorted as $kk=>$vv) {
                $relatedPages[$vv['identifier']] = $vv['page'];
                if (count($relatedPages) >= $max) {
                    break 2;
                }
            }
        }

        return $relatedPages;
    }
}
