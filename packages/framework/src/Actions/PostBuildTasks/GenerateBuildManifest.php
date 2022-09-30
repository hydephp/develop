<?php

namespace Hyde\Framework\Actions\PostBuildTasks;

use Hyde\Framework\Concerns\AbstractBuildTask;
use Hyde\Framework\Hyde;
use Illuminate\Console\OutputStyle;
use Illuminate\Support\Collection;

/**
 * @see \Hyde\Framework\Testing\Unit\GenerateBuildManifestTest
 */
class GenerateBuildManifest extends AbstractBuildTask
{
    public static string $description = 'Generating build manifest';

    public function __construct(?OutputStyle $output = null)
    {
        parent::__construct($output);
        $this->output = null;
    }

    public function run(): void
    {
        $manifest = new Collection();

        $manifest->push([
            'date' => now(),
        ]);

        /** @var \Hyde\Framework\Concerns\HydePage $page */
        foreach (Hyde::pages() as $page) {
            $manifest->push([
                'source_path' => $page->getSourcePath(),
                'output_path' => $page->getOutputPath(),
                'source_hash' => md5_file(Hyde::path($page->getSourcePath())),
                'output_hash' => $this->hashOutputPath(Hyde::sitePath($page->getOutputPath())),
            ]);
        }

        file_put_contents(Hyde::path(config(
            'hyde.build_manifest_path',
            'storage/framework/cache/build-manifest.json'
        )), $manifest->toJson());
    }

    protected function hashOutputPath(string $path): ?string
    {
        return file_exists($path) ? md5_file($path) : null;
    }
}
