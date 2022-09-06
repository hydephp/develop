<?php

namespace Hyde\Framework\Actions\PostBuildTasks;

use Hyde\Framework\Contracts\AbstractBuildTask;
use Hyde\Framework\Hyde;
use Illuminate\Console\OutputStyle;
use Illuminate\Support\Collection;

class GenerateBuildManifest extends AbstractBuildTask
{
    public static string $description = 'Generating build manifest';

    protected static string $manifestPath = 'storage/framework/cache/build-manifest.json';

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
                'input_hash' => md5(Hyde::path($page->getSourcePath())),
                'output_hash' => md5(Hyde::path($page->getOutputPath())),
            ]);
        }

        file_put_contents(Hyde::path(static::$manifestPath), $manifest->toJson());
    }

    public function then(): void
    {
        $this->createdSiteFile(static::$manifestPath)->withExecutionTime();
    }
}
