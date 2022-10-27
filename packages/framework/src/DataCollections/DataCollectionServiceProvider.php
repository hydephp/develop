<?php

declare(strict_types=1);

namespace Hyde\DataCollections;

use Hyde\DataCollections\Facades\MarkdownCollection;
use Hyde\Helpers\Features;
use Hyde\Hyde;
use Illuminate\Foundation\AliasLoader;
use Illuminate\Support\ServiceProvider;

/**
 * @see \Hyde\Framework\Testing\Feature\DataCollectionTest
 */
class DataCollectionServiceProvider extends ServiceProvider
{
    public function register()
    {
        // Register the class alias
        AliasLoader::getInstance()->alias(
            'MarkdownCollection',
            MarkdownCollection::class
        );
    }

    public function boot(): void
    {
        if (Features::hasDataCollections()) {
            // Create the _data directory if it doesn't exist
            if (! is_dir(Hyde::path('_data'))) {
                mkdir(Hyde::path('_data'));
            }
        }
    }
}
