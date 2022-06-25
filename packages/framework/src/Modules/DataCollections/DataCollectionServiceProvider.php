<?php

namespace Hyde\Framework\Modules\DataCollections;

use Illuminate\Support\ServiceProvider;
use Illuminate\Foundation\AliasLoader;
use Hyde\Framework\Hyde;

class DataCollectionServiceProvider extends ServiceProvider
{
    public function register()
    {
        // Register the class alias
        AliasLoader::getInstance()->alias(
            'Collection', Facades\MarkdownCollection::class
        );
    }

    public function boot()
    {
        // Create the _data directory if it doesn't exist
        if (!is_dir(Hyde::path('_data'))) {
            mkdir(Hyde::path('_data'));
        }
    }
}
