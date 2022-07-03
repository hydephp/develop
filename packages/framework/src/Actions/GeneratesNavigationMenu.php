<?php

namespace Hyde\Framework\Actions;

use Hyde\Framework\Helpers\Features;
use Hyde\Framework\Hyde;
use Hyde\Framework\Models\Pages\DocumentationPage;
use Hyde\Framework\Services\CollectionService;
use Illuminate\Support\Str;

/**
 * Generate the dynamic navigation menu.
 */
class GeneratesNavigationMenu
{
    /**
     * The current page route string.
     *
     * Used to check if a given link is active,
     * and more importantly it is needed to
     * assemble the relative link paths.
     *
     * @example 'posts/my-new-post.html'
     * @example 'index.html'
     *
     * @var string
     */
    public string $currentPage;

    /**
     * The created array of navigation links.
     *
     * @var array
     */
    public array $links;

    /**
     * Construct the class.
     *
     * @param  string  $currentPage
     */
    public function __construct(string $currentPage)
    {
        $this->currentPage = $currentPage;

        $this->links = $this->getLinks();
    }

    /**
     * Create the link array.
     *
     * @return array
     */
    protected function getLinks(): array
    {
        $links = $this->getLinksFromConfig();

        // Automatically add top level pages
        foreach ($this->getListOfCustomPages() as $slug) {
            $title = $this->getTitleFromSlug($slug);
            // Only add the automatic link if it is not present in the config array
            if (! in_array($title, array_column($links, 'title'))) {
                $links[] = [
                    'title' => $title,
                    'route' => $this->getRelativeRoutePathForSlug($slug),
                    'current' => $this->currentPage == $slug,
                    'priority' => $slug == 'index' ? 100 : 999,
                ];
            }
        }

        // Add extra links

        // If the documentation feature is enabled...
        if (Features::hasDocumentationPages()) {
            // And there is no link to the docs...
            if (! in_array('Docs', array_column($links, 'title'))) {
                // But a suitable file exists...
                if (file_exists(Hyde::getDocumentationPagePath('/index.md')) || file_exists(Hyde::getDocumentationPagePath('/readme.md'))) {
                    // Then we can add a link.
                    $links[] = [
                        'title' => 'Docs',
                        'route' => $this->getRelativeRoutePathForSlug(
                            file_exists(Hyde::getDocumentationPagePath('/index.md'))
                                ? DocumentationPage::getOutputDirectory().'/index'
                                : DocumentationPage::getOutputDirectory().'/readme'
                        ),
                        'current' => false,
                        'priority' => config('docs.navigation_link_priority', 1000),
                    ];
                }
            }
        }

        // Remove config defined blacklisted links
        foreach ($links as $key => $link) {
            if (in_array(Str::slug($link['title']), config('hyde.navigation_menu_blacklist', []))) {
                unset($links[$key]);
            }
        }

        // Sort

        $columns = array_column($links, 'priority');
        array_multisort($columns, SORT_ASC, $links);

        return $links;
    }

    /**
     * Get the custom navigation links from the config, if there are any.
     *
     * @return array
     */
    public function getLinksFromConfig(): array
    {
        $configLinks = config('hyde.navigation_menu_links', []);

        $links = [];

        if (sizeof($configLinks) > 0) {
            foreach ($configLinks as $link) {
                $links[] = [
                    'title' => $link['title'],
                    'route' => $link['destination'] ?? $this->getRelativeRoutePathForSlug($link['slug']),
                    'current' => isset($link['slug']) && $this->currentPage == $link['slug'],
                    'priority' =>  $link['priority'] ?? 999,
                ];
            }
        }

        return $links;
    }

    /**
     * Get the page title.
     *
     * @param  string  $slug
     * @return string
     */
    public function getTitleFromSlug(string $slug): string
    {
        if ($slug == 'index') {
            return 'Home';
        }

        return Hyde::makeTitle($slug);
    }

    /**
     * Get a list of all the top level pages.
     *
     * @return array
     */
    protected function getListOfCustomPages(): array
    {
        return array_unique(
            array_merge(
                CollectionService::getBladePageFiles(),
                CollectionService::getMarkdownPageFiles()
            )
        );
    }

    /**
     * Inject the proper number of `../` before the links.
     *
     * @param  string  $slug
     * @return string
     */
    protected function getRelativeRoutePathForSlug(string $slug): string
    {
        return Hyde::relativeLink($slug.'.html', $this->currentPage);
    }

    /**
     * Static helper to get the array of navigation links.
     *
     * @param  string  $currentPage
     * @return array
     */
    public static function getNavigationLinks(string $currentPage = 'index'): array
    {
        $generator = new self($currentPage);

        return $generator->links;
    }
}
