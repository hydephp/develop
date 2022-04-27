<?php

namespace Hyde\Framework;

use Composer\InstalledVersions;
use Hyde\Framework\Concerns\Internal\AssetManager;
use Hyde\Framework\Concerns\Internal\FileHelpers;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * General interface for Hyde services.
 *
 * @author  Caen De Silva <caen@desilva.se>
 * @copyright 2022 Caen De Silva
 * @license MIT License
 *
 * @link https://hydephp.github.io/
 */
class Hyde
{
    use FileHelpers;
    use AssetManager;

    public static function version(): string
    {
        return InstalledVersions::getPrettyVersion('hyde/framework') ?: 'unreleased';
    }

    public static function titleFromSlug(string $slug): string
    {
        return Str::title(str_replace('-', ' ', ($slug)));
    }

    public static function getLatestPosts(): Collection
    {
        $collection = new Collection();

        foreach (glob(Hyde::path('_posts/*.md')) as $filepath) {
            $collection->push((new MarkdownPostParser(basename($filepath, '.md')))->get());
        }

        return $collection->sortByDesc('matter.date');
    }
}
