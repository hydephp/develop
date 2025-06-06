# The HydeKernel

## Introduction

In the centre, or should I say _core_, of HydePHP is the HydeKernel. The kernel encapsulates a HydePHP project and
provides helpful methods for interacting with it. You can think of it as the heart of HydePHP, if you're a romantic.

The HydeKernel is so important that you have probably used it already. The main entry point for the HydePHP
API is the Hyde facade, which calls methods on the kernel.

```php
use Hyde\Hyde;
use Hyde\Foundation\HydeKernel;

Hyde::version(); // calls $HydeKernel->version()
```

The kernel is created very early on in the application lifecycle, in the `bootstrap.php` file, where it is also bound
as a singleton into the application service container.

## Accessing the Kernel

The HydeKernel is stored as a singleton in a static property in its own class and can be accessed in a few ways.

Commonly, you'll use the `Hyde` facade which forwards calls to the singleton instance.
You can also use the `hyde()` function to get the Kernel, and call methods on it.

Since the instance is also bound into the Laravel Application Service Container you can also use Dependency Injection by type-hinting the `HydeKernel::class`.

Here are some examples of how you can call methods on the Kernel. All methods call the same method on the same instance, so it's just a matter of preference.

```php
use Hyde\Hyde;
use Hyde\Foundation\HydeKernel;

Hyde::version();
Hyde::kernel()->version();
HydeKernel::getInstance()->version();
app(HydeKernel::class)->version();
hyde()->version();
```

The Kernel instance is constructed and bound in the `app/bootstrap.php` file.

## The Kernel Lifecycle

Whenever we talk about the kernel being "booted" we are talking about the kernel's role in the autodiscovery process.

You can read all about it in the [Autodiscovery Documentation](autodiscovery).

## API Reference

Since the most common way to interact with the kernel is through the Hyde facade, we will use that for the examples.
But you could just as well chain the methods on the accessed kernel singleton instance if you wanted.

<!-- Start generated docs for the HydeKernel -->

<section id="hyde-kernel-base-methods">

<!-- Start generated docs for Hyde\Foundation\HydeKernel -->
<!-- Generated by HydePHP DocGen script at 2024-07-09 07:44:59 in 3.36ms -->

#### `version()`

No description provided.

```php
Hyde::version(): string
```

#### `__construct()`

No description provided.

```php
$hyde = new HydeKernel(string $basePath): void
```

#### `features()`

No description provided.

```php
Hyde::features(): Hyde\Facades\Features
```

#### `hasFeature()`

No description provided.

```php
Hyde::hasFeature(Hyde\Enums\Feature $feature): bool
```

#### `toArray()`

Get the instance as an array.

```php
Hyde::toArray(): array<TKey, TValue>
```

<!-- End generated docs for Hyde\Foundation\HydeKernel -->

</section>

<section id="hyde-kernel-foundation-methods">

<!-- Start generated docs for Hyde\Foundation\Concerns\HandlesFoundationCollections -->
<!-- Generated by HydePHP DocGen script at 2024-07-17 15:27:42 in 0.04ms -->

#### `files()`

No description provided.

```php
Hyde::files(): Hyde\Foundation\Kernel\FileCollection
```

#### `pages()`

No description provided.

```php
Hyde::pages(): Hyde\Foundation\Kernel\PageCollection
```

#### `routes()`

No description provided.

```php
Hyde::routes(): Hyde\Foundation\Kernel\RouteCollection
```

<!-- End generated docs for Hyde\Foundation\Concerns\HandlesFoundationCollections -->

</section>

<section id="hyde-kernel-string-methods">

<!-- Start generated docs for Hyde\Foundation\Concerns\ImplementsStringHelpers -->
<!-- Generated by HydePHP DocGen script at 2024-12-22 09:14:25 in 0.07ms -->

#### `makeTitle()`

No description provided.

```php
Hyde::makeTitle(string $value): string
```

#### `makeSlug()`

No description provided.

```php
Hyde::makeSlug(string $value): string
```

#### `normalizeNewlines()`

No description provided.

```php
Hyde::normalizeNewlines(string $string): string
```

#### `stripNewlines()`

No description provided.

```php
Hyde::stripNewlines(string $string): string
```

#### `trimSlashes()`

No description provided.

```php
Hyde::trimSlashes(string $string): string
```

#### `markdown()`

No description provided.

```php
Hyde::markdown(string $text, bool $normalizeIndentation): Illuminate\Support\HtmlString
```

<!-- End generated docs for Hyde\Foundation\Concerns\ImplementsStringHelpers -->

</section>

<section id="hyde-kernel-hyperlink-methods">

<!-- Start generated docs for Hyde\Foundation\Concerns\ForwardsHyperlinks -->
<!-- Generated by HydePHP DocGen script at 2024-09-08 10:25:34 in 0.11ms -->

#### `formatLink()`

No description provided.

```php
Hyde::formatLink(string $destination): string
```

#### `relativeLink()`

No description provided.

```php
Hyde::relativeLink(string $destination): string
```

#### `asset()`

No description provided.

```php
Hyde::asset(string $name): Hyde\Support\Filesystem\MediaFile
```

- **Throws:** \Hyde\Framework\Exceptions\FileNotFoundException If the file does not exist in the `_media` source directory.

#### `url()`

No description provided.

```php
Hyde::url(string $path): string
```

#### `route()`

No description provided.

```php
Hyde::route(string $key): Hyde\Support\Models\Route
```

#### `hasSiteUrl()`

No description provided.

```php
Hyde::hasSiteUrl(): bool
```

<!-- End generated docs for Hyde\Foundation\Concerns\ForwardsHyperlinks -->

</section>

<section id="hyde-kernel-filesystem-methods">

<!-- Start generated docs for Hyde\Foundation\Concerns\ForwardsFilesystem -->
<!-- Generated by HydePHP DocGen script at 2024-08-01 10:01:06 in 0.13ms -->

#### `filesystem()`

No description provided.

```php
Hyde::filesystem(): Hyde\Foundation\Kernel\Filesystem
```

#### `path()`

No description provided.

```php
Hyde::path(string $path): string
```

#### `vendorPath()`

No description provided.

```php
Hyde::vendorPath(string $path, string $package): string
```

#### `sitePath()`

No description provided.

```php
Hyde::sitePath(string $path): string
```

#### `pathToAbsolute()`

No description provided.

```php
Hyde::pathToAbsolute(array|string $path): array|string
```

#### `pathToRelative()`

No description provided.

```php
Hyde::pathToRelative(string $path): string
```

#### `assets()`

No description provided.

```php
Hyde::assets(): \Illuminate\Support\Collection<string, \Hyde\Support\Filesystem\MediaFile>
```

<!-- End generated docs for Hyde\Foundation\Concerns\ForwardsFilesystem -->

</section>

<section id="hyde-kernel-kernel-methods">

<!-- Start generated docs for Hyde\Foundation\Concerns\ManagesHydeKernel -->
<!-- Generated by HydePHP DocGen script at 2023-03-11 11:17:34 in 0.12ms -->

#### `getInstance()`

No description provided.

```php
Hyde::getInstance(): Hyde\Foundation\HydeKernel
```

#### `setInstance()`

No description provided.

```php
Hyde::setInstance(Hyde\Foundation\HydeKernel $instance): void
```

#### `getBasePath()`

No description provided.

```php
Hyde::getBasePath(): string
```

#### `setBasePath()`

No description provided.

```php
Hyde::setBasePath(string $basePath): void
```

#### `getSourceRoot()`

No description provided.

```php
Hyde::getSourceRoot(): string
```

#### `setSourceRoot()`

No description provided.

```php
Hyde::setSourceRoot(string $sourceRoot): void
```

#### `getOutputDirectory()`

No description provided.

```php
Hyde::getOutputDirectory(): string
```

#### `setOutputDirectory()`

No description provided.

```php
Hyde::setOutputDirectory(string $outputDirectory): void
```

#### `getMediaDirectory()`

No description provided.

```php
Hyde::getMediaDirectory(): string
```

#### `setMediaDirectory()`

No description provided.

```php
Hyde::setMediaDirectory(string $mediaDirectory): void
```

#### `getMediaOutputDirectory()`

No description provided.

```php
Hyde::getMediaOutputDirectory(): string
```

<!-- End generated docs for Hyde\Foundation\Concerns\ManagesHydeKernel -->

</section>

<section id="hyde-kernel-extensions-methods">

<!-- Start generated docs for Hyde\Foundation\Concerns\ManagesExtensions -->
<!-- Generated by HydePHP DocGen script at 2023-03-11 11:17:34 in 0.12ms -->

#### `registerExtension()`

Register a HydePHP extension within the HydeKernel.

Typically, you would call this method in the register method of a service provider. If your package uses the standard Laravel (Composer) package discovery feature, the extension will automatically be enabled when the package is installed.

```php
Hyde::registerExtension(class-string&lt;\Hyde\Foundation\Concerns\HydeExtension&gt; $extension): void
```

#### `getExtension()`

Get the singleton instance of the specified extension.

```php
Hyde::getExtension(class-string&lt;T&gt; $extension): T
```

#### `hasExtension()`

Determine if the specified extension is registered.

```php
Hyde::hasExtension(class-string&lt;\Hyde\Foundation\Concerns\HydeExtension&gt; $extension): bool
```

#### `getExtensions()`

No description provided.

```php
Hyde::getExtensions(): array<\Hyde\Foundation\Concerns\HydeExtension>
```

#### `getRegisteredExtensions()`

No description provided.

```php
Hyde::getRegisteredExtensions(): array<class-string<\Hyde\Foundation\Concerns\HydeExtension>>
```

#### `getRegisteredPageClasses()`

No description provided.

```php
Hyde::getRegisteredPageClasses(): array<class-string<\Hyde\Pages\Concerns\HydePage>>
```

<!-- End generated docs for Hyde\Foundation\Concerns\ManagesExtensions -->

</section>

<section id="hyde-kernel-view-methods">

<!-- Start generated docs for Hyde\Foundation\Concerns\ManagesViewData -->
<!-- Generated by HydePHP DocGen script at 2023-03-11 11:17:34 in 0.06ms -->

#### `shareViewData()`

Share data for the page being rendered.

```php
Hyde::shareViewData(Hyde\Pages\Concerns\HydePage $page): void
```

#### `currentRouteKey()`

Get the route key for the page being rendered.

```php
Hyde::currentRouteKey(): string
```

#### `currentRoute()`

Get the route for the page being rendered.

```php
Hyde::currentRoute(): Hyde\Support\Models\Route
```

#### `currentPage()`

Get the page being rendered.

```php
Hyde::currentPage(): Hyde\Pages\Concerns\HydePage
```

<!-- End generated docs for Hyde\Foundation\Concerns\ManagesViewData -->

</section>

<section id="hyde-kernel-boot-methods">

<!-- Start generated docs for Hyde\Foundation\Concerns\BootsHydeKernel -->
<!-- Generated by HydePHP DocGen script at 2023-03-11 11:17:34 in 0.09ms -->

#### `isBooted()`

Determine if the Kernel has booted.

```php
Hyde::isBooted(): bool
```

#### `boot()`

Boot the Hyde Kernel and run the Auto-Discovery Process.

```php
Hyde::boot(): void
```

#### `booting()`

Register a new boot listener.

Your callback will be called before the kernel is booted. You can use this to register your own routes, pages, etc. The kernel instance will be passed to your callback.

```php
/** @param callable(\Hyde\Foundation\HydeKernel): void $callback */
Hyde::booting(callable(\Hyde\Foundation\HydeKernel): void): void
```

#### `booted()`

Register a new &quot;booted&quot; listener.

Your callback will be called after the kernel is booted. You can use this to run any logic after discovery has completed. The kernel instance will be passed to your callback.

```php
/** @param callable(\Hyde\Foundation\HydeKernel): void $callback */
Hyde::booted(callable(\Hyde\Foundation\HydeKernel): void): void
```

<!-- End generated docs for Hyde\Foundation\Concerns\BootsHydeKernel -->

</section>

<!-- End generated docs for the HydeKernel -->
