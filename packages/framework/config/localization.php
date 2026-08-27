<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Site Languages
    |--------------------------------------------------------------------------
    |
    | If you want your site to be available in multiple languages, you can list
    | the language codes here. Each page will then be compiled once for every
    | language, into a subdirectory named after it, for example, /en/index.html.
    |
    | Languages can also be given display names, for the language switcher to
    | show, by listing them as 'en' => 'English' instead of just 'en'.
    |
    | Translation strings are loaded from the lang/{language} directories,
    | and can be used in your pages with the standard __() helper.
    |
    | Leaving this array empty disables localization, meaning that your site
    | is compiled as normal, with pages being placed in the site webroot.
    |
    */

    'languages' => [
        //
    ],

    /*
    |--------------------------------------------------------------------------
    | Localized Source Directory
    |--------------------------------------------------------------------------
    |
    | A page is authored once, and is by default compiled into every language,
    | using the translation strings of each. When a page needs genuinely
    | different content in a language, you can place a companion source
    | file for it in this directory, under the language it is for.
    |
    | For example, _locales/sv/_pages/about.md supplies the Swedish content
    | for _pages/about.md. Pages with no companion file for a language
    | fall back to their canonical source, rendered in that language.
    |
    */

    'source_directory' => '_locales',
];
