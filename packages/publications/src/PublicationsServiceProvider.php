<?php

declare(strict_types=1);

namespace Hyde\Publications;

use Hyde\Facades\Filesystem;
use Hyde\Foundation\HydeKernel;
use Hyde\Publications\Models\PublicationType;
use Hyde\Publications\Providers\TranslationServiceProvider;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationServiceProvider;

use function storage_path;

class PublicationsServiceProvider extends ServiceProvider
{
    /**
     * Register the application services.
     */
    public function register(): void
    {
        $this->app->make(HydeKernel::class)->registerExtension(PublicationsExtension::class);

        $this->commands([
            Commands\MakePublicationTagCommand::class,
            Commands\MakePublicationTypeCommand::class,
            Commands\MakePublicationCommand::class,

            Commands\ValidatePublicationTypesCommand::class,
            Commands\ValidatePublicationsCommand::class,
            Commands\SeedPublicationCommand::class,
        ]);

        $this->registerAdditionalServiceProviders();
    }

    /**
     * Bootstrap the application services.
     */
    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'hyde-publications');

        HydeKernel::getInstance()->booting(function () {
            PublicationService::getPublicationTypes()->each(function (PublicationType $type): void {
                static::makePublicationTypeClass($type);
            });
        });
    }
    /**
     * Register additional service providers.
     */
    protected function registerAdditionalServiceProviders(): void
    {
        $this->app->register(TranslationServiceProvider::class);
        $this->app->register(ValidationServiceProvider::class);
    }

    protected static function makePublicationTypeClass(PublicationType $type): void
    {
        // Todo cache using schema hash

        $className = Str::studly($type->getIdentifier());

        $class = <<<EOT
<?php

use Hyde\Publications\Models\PublicationPage;

/** @autogenerated by HydePHP from {$type->getIdentifier()}/schema.json */
class {$className}PublicationPage extends PublicationPage
{
    public static string \$publicationType = '{$type->getIdentifier()}';
    
    public static string \$sourceDirectory = '{$type->getIdentifier()}';
    public static string \$outputDirectory = '{$type->getIdentifier()}';
    public static string \$template = '{$type->detailTemplate}';

    public function getPublicationType(): string
    {
        return static::\$publicationType;
    }
}

EOT;

        $classPath = storage_path("framework/cache/publications/{$className}.php");
        Filesystem::ensureDirectoryExists(dirname($classPath));
        file_put_contents(
            $classPath,
            $class
        );

        // load the class
        require_once $classPath;

        // register the class
        PublicationsExtension::$pageClasses = array_merge(
            PublicationsExtension::$pageClasses,
             [$className]
        );
    }
}
