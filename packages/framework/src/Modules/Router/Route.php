<?php

namespace Hyde\Framework\Modules\Router;

use Hyde\Framework\Hyde;
use Hyde\Framework\Modules\Router\Concerns\RouteContract;

class Route implements RouteContract
{
    /**
     * @var string<\Hyde\Framework\Contracts\AbstractPage> $sourceModel
     */
    protected string $sourceModel;

    /**
     * @var string relative path to the source file
     */
    protected string $sourceFile;

    /**
     * @var string the calculated route key/name
     */
    protected string $name;

    /**
     * @var string the calculated HTML file path
     */
    protected string $path;


    /**
     * @var string the route group
     */
    protected string $group;

    /**
     * @param string <\Hyde\Framework\Contracts\AbstractPage> $sourceModel
     */
    public function __construct(string $sourceModel, string $sourceFile)
    {
        $this->sourceModel = $sourceModel;
        $this->sourceFile = $sourceFile;

        $this->name = $this->generateRouteName();
        $this->path = $this->generateOutputPath();
        $this->group = $this->assignRouteGroup();
    }

    /**
     * @return string the calculated route key/name
     */
    protected function generateRouteName(): string
    {
        return $this->getGroup() . '.' . $this->baseName();
    }

    /**
     * @return string the absolute path to the output HTML file
     * @usage for the static site builder to output the file
     * @usage can also be used to calculate relative links
     */
    protected function generateOutputPath(): string
    {
        return Hyde::getSiteOutputPath($this->sourceModel::$outputDirectory . $this->baseName() . '.html');
    }

    /**
     * @return string the route group
     */
    protected function assignRouteGroup(): string
    {
        return trim($this->sourceModel::$sourceDirectory, '_\\/');
    }

    /**
     * @return string the path for the compiled HTML file relative to the _site directory
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * @return string the generated route name in dot notation
     * @example 'pages.about' for source file '_pages/about.md'
     * @usage is used to retrieve a route from the route index
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string the route group
     * @example 'pages' for source file '_pages/about.md'
     * @usage is useful to sort Collection routes by group
     */
    public function getGroup(): string
    {
        return $this->group;
    }

    /**
     * @return string the base name of the file
     */
    public function baseName(): string
    {
        return basename($this->sourceFile, $this->sourceModel::$fileExtension);
    }

    public function __toString(): string
    {
        return $this->path;
    }
}