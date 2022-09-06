<?php

namespace Hyde\Framework\Actions\PostBuildTasks;

use Hyde\Framework\Contracts\AbstractBuildTask;
use Hyde\Framework\Hyde;
use Illuminate\Console\OutputStyle;
use Illuminate\Support\Collection;

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

        /** @var \Hyde\Framework\Contracts\AbstractPage $page */
        foreach (Hyde::pages() as $page) {
            $manifest->push([
                'page' => $page->getSourcePath(),
                'source_hash' => md5_file(Hyde::path($page->getSourcePath())),
                'output_hash' => md5_file(Hyde::getSiteOutputPath($page->getOutputPath())),
            ]);
        }

        file_put_contents(Hyde::path(config('hyde.build_manifest_path',
            'storage/framework/cache/build-manifest.json')
        ), $manifest->toJson());
    }
}
