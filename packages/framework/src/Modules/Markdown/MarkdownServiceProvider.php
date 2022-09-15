<?php

namespace Hyde\Framework\Modules\Markdown;

use Hyde\Framework\Actions\MarkdownConverter;
use Illuminate\Support\ServiceProvider;

class MarkdownServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(MarkdownConverter::class, function () {
            return new MarkdownConverter();
        });
    }
}
