<?php

namespace Hyde\Framework\Services;

use Hyde\Framework\StaticPageBuilder;

/**
 * Build static pages, but intelligently.
 *
 * Runs the static page builder for a given path.
 */
class RebuildService
{
    /**
     * The source file to build.
     * Should be relative to the Hyde installation.
     *
     * @var string
     */
    public string $filepath;

    /**
     * The model of the source file.
     *
     * @var string
     *
     * @internal
     */
    public string $model;

    /**
     * The page builder instance.
     * Used to get debug output from the builder.
     *
     * @var StaticPageBuilder
     */
    public StaticPageBuilder $builder;

    /**
     * Construct the service class instance.
     *
     * @param  string  $filepath Relative source file to compile. Example: _posts/foo.md
     */
    public function __construct(string $filepath)
    {
        $this->filepath = $filepath;
        $this->model = DiscoveryService::findModelFromFilePath($this->filepath);
    }

    /**
     * Execute the service action.
     */
    public function execute(): StaticPageBuilder
    {
        return $this->builder = (new StaticPageBuilder(
            DiscoveryService::getParserInstanceForModel(
                $this->model,
                basename(
                    str_replace(
                        DiscoveryService::getFilePathForModelClassFiles($this->model).'/',
                        '',
                        $this->filepath
                    ),
                    DiscoveryService::getFileExtensionForModelFiles($this->model)
                )
            )->get(),
            true
        ));
    }
}
