<?php

/*
|--------------------------------------------------------------------------
| Documentation Module Settings
|--------------------------------------------------------------------------
|
| Since the Hyde documentation module has many configuration options,
| they have now been broken out into their own configuration file.
|
*/

return [
    /*
    |--------------------------------------------------------------------------
    | Sidebar Header Name
    |--------------------------------------------------------------------------
    |
    | By default, the sidebar title shown in the documentation page layouts uses
    | the app name suffixed with "docs". You can change it with this setting.
    |
    */

    'header_title' => env('SITE_NAME', 'HydePHP').' Docs',

    /*
    |--------------------------------------------------------------------------
    | Sidebar Footer
    |--------------------------------------------------------------------------
    |
    | By default, there is a small footer in the sidebar that links to your home page.
    | If this is not your cup of tea, you can disable it by setting the option below to false.
    |
    */

    'sidebar_footer' => true,

    /*
    |--------------------------------------------------------------------------
    | Collaborative Source Editing Location
    |--------------------------------------------------------------------------
    |
    | @see https://hydephp.com/docs/master/documentation-pages#automatic-edit-page-button
    |
    | By adding a base URL here, Hyde will use it to create "edit source" links
    | to your documentation pages. Hyde expects this to be a GitHub path, but
    | it will probably work with other methods as well, if not, send a PR!
    |
    | You can also change the link text with the `edit_source_link_text` setting.
    |
    | Example: https://github.com/hydephp/docs/blob/master
    |          Do not specify the filename or extension, Hyde will do that for you.
    | Setting the setting to null will disable the feature.
    |
    */

    // 'source_file_location_base' => 'https://github.com/<user>/<repo>/<[blob/edit]>/<branch>',
    'edit_source_link_text' => 'Edit Source',
    'edit_source_link_position' => 'footer', // 'header', 'footer', or 'both'

    /*
    |--------------------------------------------------------------------------
    | Sidebar Page Order
    |--------------------------------------------------------------------------
    |
    | In the generated Documentation pages the navigation links in the sidebar
    | default to sort alphabetically. You can reorder the page identifiers
    | in the list below, and the links will get sorted in that order.
    |
    | Internally, the items listed will get a position priority of 500 + the order its found in the list.
    | Link items without an entry here will have fall back to the default priority of 999, putting them last.
    |
    | You can also set explicit priorities in front matter.
    |
    */

    'sidebar_order' => [
        'readme',
        'installation',
        'getting-started',
    ],

    /*
    |--------------------------------------------------------------------------
    | Table of Contents Settings
    |--------------------------------------------------------------------------
    |
    | The Hyde Documentation Module comes with a fancy Sidebar that, by default,
    | has a Table of Contents included. Here, you can configure its behavior,
    | content, look and feel. You can also disable the feature completely.
    |
    */

    'table_of_contents' => [
        'enabled' => true,
        'min_heading_level' => 2,
        'max_heading_level' => 4,
    ],

    /*
    |--------------------------------------------------------------------------
    | Search Customization
    |--------------------------------------------------------------------------
    |
    | Hyde comes with an easy to use search feature for documentation pages.
    | @see https://hydephp.com/docs/master/documentation-pages#search-feature
    |
    */

    // Should a docs/search.html page be generated?
    'create_search_page' => true,

    // Are there any pages you don't want to show in the search results?
    'exclude_from_search' => [
        'changelog',
    ],

];
