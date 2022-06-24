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
        //
    }
}
