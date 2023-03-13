---
navigation:
    label: "Advanced Customization"
    priority: 30
---

# Advanced Customization

## Introduction & Warning

>danger Danger lies ahead! Read this before you proceed.

This page covers advanced usage of potentially experimental and unstable features and is intended for developers
who know what they are doing and can handle the risk of breaking things. The article will also cover things
that you _can_ do, but that you maybe should not. With great power comes great responsibility. You have been warned.

Documentation here will be mainly example driven, as it is assumed you have somewhat of an understanding of what you are doing already.

### Emoji legend

Each section is marked with an emoji that indicates the level of risk. Note that pretty much all of these
are experimental features, and are not at all supported. Use at your own risk.

- 🧪 = Indicates experimental features bound to change at any time.
- ⚠ = Exercise caution when using this feature.
- 💔 = This could seriously break things


## Customizing source directories 🧪

The source directory paths are stored in the PageModel objects.
You can change them by modifying the static property, for example in a service provider.

Internally, the paths are registered in the HydeServiceProvider using the following method:

```php
// filepath Hyde\Framework\HydeServiceProvider
use Hyde\Framework\Concerns\RegistersFileLocations;

public function register(): void
{
    $this->registerSourceDirectories([
        BladePage::class => '_pages',
        MarkdownPage::class => '_pages',
        MarkdownPost::class => '_posts',
        DocumentationPage::class => '_docs',
    ]);
}
```


## Custom source root directory 🧪

HydePHP will by default look for the underscored source directories in the root of your project.
If you're not happy with this, it's easy to change! For example, you might want everything in a 'src'
subdirectory. That's easy enough, just set the value of the `source_root` setting in config/hyde.php to `'src'`!

### Automatic change 🧪

You can even make this change automatically with the `php hyde change:sourceDirectory` command!

When run, Hyde will update the source directory setting in the config file, then create the directory if it doesn't exist, then move all source directories into it.


## Custom media directory 🧪

The media directory houses assets like images and stylesheets. The default directory is `_media`, and upon building the site,
Hyde will copy all files in this directory to `_site/media` (or whatever your configured output and media directories are).

You can change the path to this directory by setting the `media_directory` setting in `config/hyde.php`.
Note that this change will affect both the source and output directories. For example, if you set the value to `assets`,
all files from `assets` will be copied to `_site/assets`.

If the setting starts with an underscore, that will be removed from the output directory, so files in `_assets` will be copied to `_site/assets`.

>info Note that you will likely need to manually update `webpack.mix.js` so Laravel Mix can compile the assets correctly.

>info You will of course also need to copy over any existing files from the old directory to the new one.


## Customizing the output directory ⚠

>danger Hyde deletes all files in the output directory before compiling the site. Don't set this path to a directory that contains important files!

If you want to store your compiled website in a different directory than
the default `_pages`, you can change the path using the following configuration option in config/hyde.php. The path is expected to be relative to your project root.

```php
// filepath config/hyde.php
return [
    'output_directory' => 'docs',
];
```
