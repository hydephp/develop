<?php

namespace Hyde\Framework\Modules\DataCollections;

use Illuminate\Foundation\AliasLoader;
use Illuminate\Support\ServiceProvider;

class DataCollectionServiceProvider extends ServiceProvider
{
    public function register()
    {
        // Register the class alias
        AliasLoader::getInstance()->alias(
            'Collection', DataCollection::class
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
