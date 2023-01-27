<?php

declare(strict_types=1);

namespace Hyde\Framework\Features\DataCollections;

use Hyde\Framework\Features\DataCollections\Facades\MarkdownCollection;
use Illuminate\Foundation\AliasLoader;
use Illuminate\Support\ServiceProvider;

/**
 * @deprecated (Deprecation Candidate) If the ensureDirectoryExists method is removed, so can this provider, as the only function left is an alias which could be handled in the app config.
 * @see \Hyde\Framework\Testing\Feature\DataCollectionTest
 */
class DataCollectionServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Register the class alias
        AliasLoader::getInstance()->alias(
            'MarkdownCollection',
            MarkdownCollection::class
        );
    }

    public function boot(): void
    {
        //
    }
}
