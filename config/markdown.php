<?php

/*
|--------------------------------------------------------------------------
| Markdown Configuration
|--------------------------------------------------------------------------
|
| HydePHP makes heavy use of Markdown. In this file you can configure
| Markdown related services, as well as change the extensions used.
|
*/

return [

    /*
    |--------------------------------------------------------------------------
    | Markdown Extensions
    |--------------------------------------------------------------------------
    |
    | Define any extra extensions that should be loaded into the CommonMark
    | converter. Should be fully qualified class names to the extension.
    |
    | Remember that you may need to install any third party extensions
    | through Composer before you can use them.
    |
    | Hyde ships with the GitHub Flavored Markdown extension.
    | The Torchlight extension is enabled automatically when needed.
    |
    */

    'extensions' => [
        \League\CommonMark\Extension\GithubFlavoredMarkdownExtension::class,
        \League\CommonMark\Extension\Attributes\AttributesExtension::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Configuration Options
    |--------------------------------------------------------------------------
    |
    | Define any options that should be passed to the CommonMark converter.
    |
    | Hyde handles many of the options automatically, but you may want to
    | override some of them and/or add your own. Any custom options
    | will be merged with the Hyde defaults during runtime.
    |
    */

    'config' => [
        //
    ],

    /*
    |--------------------------------------------------------------------------
    | Allow all HTML tags
    |--------------------------------------------------------------------------
    |
    | HydePHP uses the GitHub Flavored Markdown extension to convert Markdown.
    | This, by default strips out some HTML tags. If you want to allow all
    | arbitrary HTML tags, and understand the risks involved, you can
    | use this config setting to enable all HTML tags.
    |
    */

    'allow_html' => false,

    /*
    |--------------------------------------------------------------------------
    | Blade-supported Markdown
    |--------------------------------------------------------------------------
    |
    | This feature allows you to use basic Laravel Blade in Markdown files.
    |
    | BladeDown is enabled by default because source files in Hyde projects are
    | generally trusted and reviewed. Since Blade can execute arbitrary PHP,
    | disable this when compiling untrusted or unreviewed Markdown.
    |
    | To see the syntax and usage, see the documentation:
    | @see https://hydephp.com/docs/3.x/advanced-markdown#using-blade-in-markdown
    |
    */

    'enable_blade' => true,

    /*
    |--------------------------------------------------------------------------
    | Blade Block Support
    |--------------------------------------------------------------------------
    |
    | A sister feature to the Blade support above, letting you render Blade using
    | fenced code blocks instead of the [Blade]: directive.
    |
    | It carries the same security caveat since it allows arbitrary PHP to run,
    | so it's disabled by default. Only enable it if your Markdown is trusted.
    |
    | To see the syntax and usage, see the documentation.
    |
    */

    'enable_blade_blocks' => false,

    /*
    |--------------------------------------------------------------------------
    | Tailwind Typography Prose Classes
    |--------------------------------------------------------------------------
    |
    | HydePHP uses Tailwind Typography to style rendered Markdown.
    |
    | This setting controls the base classes to apply to all the HTML elements
    | containing rendered markdown. Please note that if you add any new
    | classes, you may need to recompile your CSS file.
    |
    */

    'prose_classes' => 'prose dark:prose-invert',

    /*
    |--------------------------------------------------------------------------
    | Heading Permalinks Configuration
    |--------------------------------------------------------------------------
    |
    | Here you can specify which page classes should have heading permalinks.
    | By default, only documentation pages have permalinks enabled, but you
    | are free to enable it for any kind of page by adding the page class.
    |
    */

    'permalinks' => [
        'pages' => [
            \Hyde\Pages\DocumentationPage::class,
        ],
    ],

];
