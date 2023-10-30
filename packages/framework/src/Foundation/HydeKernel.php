<?php

declare(strict_types=1);

namespace Hyde\Foundation;

use Hyde\Facades\Features;
use Hyde\Foundation\Kernel\Filesystem;
use Hyde\Foundation\Kernel\Hyperlinks;
use Hyde\Foundation\Kernel\FileCollection;
use Hyde\Foundation\Kernel\PageCollection;
use Hyde\Foundation\Kernel\RouteCollection;
use Hyde\Support\Contracts\SerializableContract;
use Hyde\Support\Concerns\Serializable;
use Illuminate\Support\Traits\Macroable;

/**
 * Encapsulates a HydePHP project, providing helpful methods for interacting with it.
 *
 * @see \Hyde\Hyde for the facade commonly used to access this class.
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
class HydeKernel implements SerializableContract
{
    use Concerns\HandlesFoundationCollections;
    use Concerns\ImplementsStringHelpers;
    use Concerns\ForwardsHyperlinks;
    use Concerns\ForwardsFilesystem;
    use Concerns\ManagesHydeKernel;
    use Concerns\ManagesExtensions;
    use Concerns\ManagesViewData;
    use Concerns\BootsHydeKernel;

    use Serializable;
    use Macroable;

    final public const VERSION = '1.3.0';

    protected static self $instance;

    protected string $basePath;
    protected string $sourceRoot = '';
    protected string $outputDirectory = '_site';
    protected string $mediaDirectory = '_media';

    protected Filesystem $filesystem;
    protected Hyperlinks $hyperlinks;

    protected FileCollection $files;
    protected PageCollection $pages;
    protected RouteCollection $routes;

    protected bool $booted = false;

    /** @var array<class-string<\Hyde\Foundation\Concerns\HydeExtension>, \Hyde\Foundation\Concerns\HydeExtension> */
    protected array $extensions = [];

    public function __construct(?string $basePath = null)
    {
        $this->setBasePath($basePath ?? getcwd());
        $this->filesystem = new Filesystem($this);
        $this->hyperlinks = new Hyperlinks($this);

        $this->registerExtension(HydeCoreExtension::class);
    }

    public static function version(): string
    {
        return self::VERSION;
    }

    public function features(): Features
    {
        return new Features;
    }

    public function hasFeature(string $feature): bool
    {
        return Features::enabled($feature);
    }

    /** @inheritDoc */
    public function toArray(): array
    {
        return [
            'basePath' => $this->basePath,
            'sourceRoot' => $this->sourceRoot,
            'outputDirectory' => $this->outputDirectory,
            'mediaDirectory' => $this->mediaDirectory,
            'extensions' => $this->getRegisteredExtensions(),
            'features' => $this->features(),
            'files' => $this->files(),
            'pages' => $this->pages(),
            'routes' => $this->routes(),
        ];
    }
}
