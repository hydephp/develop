<?php

/*
|--------------------------------------------------------------------------
|      __ __        __    ___  __ _____
|     / // /_ _____/ /__ / _ \/ // / _ \
|    / _  / // / _  / -_) ___/ _  / ___/
|   /_//_/\_, /\_,_/\__/_/  /_//_/_/
|        /___/
|--------------------------------------------------------------------------
|
| Welcome to HydePHP! In this file, you can customize your new Static Site!
|
| HydePHP favours convention over configuration and as such requires virtually
| no configuration out of the box to get started. Though, you may want to
| change the options to personalize your site and make it your own!
|
*/

return [
    'name' => env('SITE_NAME', 'HydePHP Canary Preview'),
    'url' => env('SITE_URL', 'http://localhost'),
    'pretty_urls' => false,
    'generate_sitemap' => true,
    'generate_rss_feed' => true,
    'rss_filename' => 'feed.xml',
    'language' => 'en',
    'output_directory' => '_site',
];
