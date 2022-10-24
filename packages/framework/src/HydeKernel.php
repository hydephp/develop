<?php

namespace Hyde\Framework;

use Composer\InstalledVersions;
use Hyde\Framework\Concerns\JsonSerializesArrayable;
use Hyde\Framework\Foundation\FileCollection;
use Hyde\Framework\Foundation\Filesystem;
use Hyde\Framework\Foundation\Hyperlinks;
use Hyde\Framework\Foundation\PageCollection;
use Hyde\Framework\Foundation\RouteCollection;
use Hyde\Framework\Helpers\Features;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Traits\Macroable;

/**
 * Encapsulates a HydePHP project, providing helpful methods for interacting with it.
 *
 * @see \Hyde\Framework\Hyde for the facade commonly used to access this class.
 *
 * @author  Caen De Silva <caen@desilva.se>
 * @copyright 2022 Caen De Silva
 * @license MIT License
 *
 * @link https://hydephp.com/
 *
 * @extra Usage information:
 *
 * The HydeKernel It is stored as a singleton in this class, and is bound into the
 * Laravel Application Service Container, and can be accessed in a few ways.
 *
 * Commonly, you'll use the Hyde facade, but you can also use Dependency Injection
 * by type-hinting the HydeKernel::class, or use the hyde() function to get the Kernel.
 *
 * The Kernel instance is constructed in bootstrap.php, and is available globally as $hyde.
 */
class HydeKernel implements Arrayable, \JsonSerializable
{
    use Foundation\Concerns\HandlesFoundationCollections;
    use Foundation\Concerns\ImplementsStringHelpers;
    use Foundation\Concerns\ForwardsHyperlinks;
    use Foundation\Concerns\ForwardsFilesystem;
    use Foundation\Concerns\ManagesHydeKernel;
    use Foundation\Concerns\ManagesViewData;

    use JsonSerializesArrayable;
    use Macroable;

    protected static HydeKernel $instance;

    protected string $basePath;

    protected Filesystem $filesystem;
    protected Hyperlinks $hyperlinks;

    protected FileCollection $files;
    protected PageCollection $pages;
    protected RouteCollection $routes;

    protected bool $booted = false;

    public const VERSION = '0.64.0-dev';

    public function __construct(?string $basePath = null)
    {
        $this->setBasePath($basePath ?? getcwd());
        $this->filesystem = new Filesystem($this);
        $this->hyperlinks = new Hyperlinks($this);
    }

    public static function version(): string
    {
        return InstalledVersions::getPrettyVersion('hyde/framework') ?: 'unreleased';
    }

    public function features(): Features
    {
        return new Features;
    }

    public function hasFeature(string $feature): bool
    {
        return Features::enabled($feature);
    }

    /**
     * @inheritDoc
     * @psalm-return array{basePath: string, features: \Hyde\Framework\Helpers\Features, pages: \Hyde\Framework\Foundation\PageCollection, routes: \Hyde\Framework\Foundation\RouteCollection}
     */
    public function toArray(): array
    {
        return [
            'basePath' => $this->basePath,
            'features' => $this->features(),
            'files' => $this->files(),
            'pages' => $this->pages(),
            'routes' => $this->routes(),
        ];
    }
}
