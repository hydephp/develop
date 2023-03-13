<?php

declare(strict_types=1);

namespace Hyde\Framework\Actions\PostBuildTasks;

use Hyde\Hyde;
use Hyde\Facades\Config;
use Hyde\Pages\Concerns\HydePage;
use Hyde\Framework\Features\BuildTasks\PostBuildTask;
use Illuminate\Console\OutputStyle;
use Illuminate\Support\Collection;

use function Hyde\unixsum_file;
use function file_put_contents;
use function file_exists;
use function json_encode;
use function now;

/**
 * The build manifest contains a list of all pages and their source and output paths.
 *
 * While not used by the framework, it's useful for addon services to know which page were built, and when.
 * The hashes are so that the addon services can determine if a page has changed since the last build.
 *
 * The manifest is stored in the `app/storage/framework/cache` directory by default, as some users
 * may not want to commit the manifest file to their repository or their deployed site.
 * However, a great alternate location is in `_site/build-manifest.json`,
 * if you don't mind it the file being publicly accessible.
 *
 * @see \Hyde\Framework\Testing\Unit\GenerateBuildManifestTest
 */
class GenerateBuildManifest extends PostBuildTask
{
    public static string $message = 'Generating build manifest';

    public function handle(): void
    {
        $pages = new Collection();

        /** @var \Hyde\Pages\Concerns\HydePage $page */
        foreach (Hyde::pages() as $page) {
            $pages->put($page->getRouteKey(), [
                'source_path' => $page->getSourcePath(),
                'output_path' => $page->getOutputPath(),
                'source_hash' => $this->hashSourcePath($page),
                'output_hash' => $this->hashOutputPath($page),
            ]);
        }

        file_put_contents($this->getManifestPath(), $this->jsonEncodeOutput($pages));
    }

    protected function hashOutputPath(HydePage $page): ?string
    {
        $path = Hyde::sitePath($page->getOutputPath());

        return file_exists($path) ? unixsum_file($path) : null;
    }

    protected function hashSourcePath(HydePage $page): string
    {
        return unixsum_file(Hyde::path($page->getSourcePath()));
    }

    protected function getManifestPath(): string
    {
        return Hyde::path(Config::getString(
            'hyde.build_manifest_path',
            'app/storage/framework/cache/build-manifest.json'
        ));
    }

    protected function jsonEncodeOutput(Collection $pages): string
    {
        return json_encode([
            'generated' => now(),
            'pages' => $pages,
        ], JSON_PRETTY_PRINT);
    }

    public function setOutput(OutputStyle $output)
    {
        // Disable output
    }
}
