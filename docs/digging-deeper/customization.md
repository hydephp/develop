---
navigation:
    label: "Customizing your Site"
    priority: 25
---

# Customizing your Site

## Introduction

Hyde favours <a href="https://en.wikipedia.org/wiki/Convention_over_configuration">"Convention over Configuration"</a>
and comes preconfigured with sensible defaults. However, Hyde also strives to be modular and endlessly customizable
if you need it. This page guides you through the many options available!

All the configuration files are stored in the config directory, and allow you to customize almost all aspects of your site.
Each option is documented, so feel free to look through the files and get familiar with the options available to you.


## Accessing Configuration Values

### Configuration API Recap

HydePHP uses the same configuration system as Laravel. Here's a quick recap from the [Laravel Documentation](https://laravel.com/docs/10.x/configuration):

You may easily access your configuration values using the global `config` function from anywhere in your project code.
The configuration values may be accessed using "dot notation" syntax, which includes the name of the file and option you wish to access.

```php
$value = config('hyde.name');
```

A default value may also be specified and will be returned if the configuration option does not exist:

```php
$value = config('hyde.name', 'HydePHP');
```

HydePHP also provides a strongly typed `Config` facade which extends the Laravel `Config` facade, but allows strict types:

```php
use Hyde\Facades\Config;

// Will always return a string, or it throws a TypeError
$name = Config::getString('hyde.name', 'HydePHP'): string;
```

### Dot Notation

As seen in the example above, when referencing configuration options, we often use "dot notation" to specify the configuration file.
For example, `config('hyde.name')` means that we are looking for the `name` option in the `config/hyde.php` file.

### Front Matter or Configuration Files?

In some cases, the same options can be set in the front matter of a page or in a configuration file. Both ways are always documented, and it's up to you to choose which one you prefer. Note that in most cases, if a setting is set in both the front matter and the configuration file, the front matter setting will take precedence.

### A note on file paths

When Hyde references files, especially when passing filenames between components, the file path is almost always
relative to the root of the project. Specifying absolute paths yourself could lead to unforeseen problems.


## Configuration Files Overview

There are a few configuration files available in the `config` directory. All options are documented, so feel free to look through the files and get familiar with the options available to you.

Below are two tables over the different configuration files. Click on a file name to see the default file on GitHub.

### HydePHP Configuration Files

These are the main configuration files for HydePHP and lets you customize the look and feel of your site, as well as the behaviour of HydePHP itself.
The main configuration file, `hyde.php`, is used for things ranging from site name and base URL to navigation menus and what features to enable.

| Config File                                                                                                        | Description                                                                                       |
|--------------------------------------------------------------------------------------------------------------------|---------------------------------------------------------------------------------------------------|
| <a href="https://github.com/hydephp/hyde/blob/master/config/hyde.php" rel="nofollow noopener">hyde.php</a>         | Main HydePHP configuration file for customizing the overall project.                              |
| <a href="https://github.com/hydephp/hyde/blob/master/config/docs.php" rel="nofollow noopener">docs.php</a>         | Options for the HydePHP documentation site generator module.                                      |
| <a href="https://github.com/hydephp/hyde/blob/master/config/markdown.php" rel="nofollow noopener">markdown.php</a> | Configure Markdown related services, as well as change the CommonMark extensions.                 |
| <a href="https://github.com/hydephp/hyde/blob/master/app/config.php" rel="nofollow noopener">app/config.php</a>    | Configures the underlying Laravel application. (Commonly found as config/app.php in Laravel apps) |

>info Tip: The values in `hyde.php` can also be set in YAML by creating a `hyde.yml` file in the root of your project. See [#yaml-configuration](#yaml-configuration) for more information.

### Publishable Laravel & Package Configuration Files

Since HydePHP is based on Laravel we also have a few configuration files related to them. As you most often don't need
to edit any of these, unless you want to make changes to the underlying application, they are not present in the
base HydePHP installation. However, you can publish them to your project by running `php hyde publish:configs`.

| Config File                                                                                                            | Description                                                             |
|------------------------------------------------------------------------------------------------------------------------|-------------------------------------------------------------------------|
| <a href="https://github.com/hydephp/hyde/blob/master/config/view.php" rel="nofollow noopener">view.php</a>             | Configures the paths for the Blade View compiler.                       |
| <a href="https://github.com/hydephp/hyde/blob/master/config/cache.php" rel="nofollow noopener">cache.php</a>           | Configures the cache driver and cache path locations.                   |
| <a href="https://github.com/hydephp/hyde/blob/master/config/commands.php" rel="nofollow noopener">commands.php</a>     | Configures the Laravel Zero commands for the HydeCLI.                   |
| <a href="https://github.com/hydephp/hyde/blob/master/config/torchlight.php" rel="nofollow noopener">torchlight.php</a> | Configures settings for the Torchlight syntax highlighting integration. |

{.align-top}

If any of these files are missing, you can run `php hyde publish:configs` to copy the default files to your project.


## Configuration Options

While all options are already documented within the files, here are some further explanations of some of the options.

### RSS feed generation

When enabled, an RSS feed containing all your Markdown blog posts will be generated when you compile your static site.
Here are the default settings:

```php
// filepath config/hyde.php
'rss' => [
    // Should the RSS feed be generated?
    'enabled' => true,

    // What filename should the RSS file use?
    'filename' => 'feed.xml',

    // The channel description.
    'description' => env('SITE_NAME', 'HydePHP').' RSS Feed',
],
```

>warning Note that this feature requires that a site `url` is set!

### Authors

Hyde has support for adding authors in front matter, for example to
automatically add a link to your website or social media profiles.
However, it's tedious to have to add those to each and every
post you make, and keeping them updated is even harder.

You can predefine authors in the Hyde config.
When writing posts, just specify the username in the front matter,
and the rest of the data will be pulled from a matching entry.

#### Example

```php
// torchlight! {"lineNumbers": false}
'authors' => [
    Author::create(
        username: 'mr_hyde', // Required username
        name: 'Mr. Hyde', // Optional display name
        website: 'https://hydephp.com' // Optional website URL
    ),
],
```

This is equivalent to the following front matter in a blog post:

```yaml
author:
    username: mr_hyde
    name: Mr. Hyde
    website: https://hydephp.com
```

But you only have to specify the username:

```yaml
author: mr_hyde
```

### Footer

Most websites have a footer with copyright details and contact information. You probably want to change the Markdown to include your information, though you are of course welcome to keep the default attribution link!

The footer component is made up of a few levels of components, depending on how much you want to customize.

#### Customizing the Markdown text

There are two ways to customize the footer text. First, you can set it in the configuration file:

```php
// filepath: config/hyde.php
'footer' => 'Site proudly built with [HydePHP](https://github.com/hydephp/hyde) 🎩',
```

If you don't want to write Markdown in the configuration file, you can create a Markdown file in your includes directory. When this file is found, it will be used instead of the configuration setting.

```markdown
// filepath: resources/includes/footer.md
Site proudly built with [HydePHP](https://github.com/hydephp/hyde) 🎩
```

In both cases the parsed Markdown will be rendered in the footer Blade component.

#### Customizing the Blade component

The actual footer component is rendered using the [`layouts/footer.blade.php`](https://github.com/hydephp/framework/blob/master/resources/views/layouts/footer.blade.php) Blade template.

In this template we automatically render the configured footer Markdown text. If you want to change this behaviour, for example, HydePHP.com uses a more sophisticated footer, simply [publish the footer component](#blade-views).

#### Disabling the footer entirely

If you don't want to have a footer on your site, you can set the `'footer'` configuration option to `false`.

```php
// filepath: config/hyde.php
'footer' => 'false',
```

### Navigation Menu & Sidebar

A great time-saving feature of HydePHP is the automatic navigation menu and documentation sidebar generation.
Hyde is designed to automatically configure these menus for you based on the content you have in your project.

Still, you will likely want to customize some parts of these menus, and thankfully, Hyde makes it easy to do so.

#### Customizing the navigation menu

- To customize the navigation menu, use the setting `navigation.order` in the `hyde.php` config.
- When customizing the navigation menu, you should use the [route key](core-concepts#route-keys) of the page.

Learn more in the [Navigation Menu](navigation) documentation.

#### Customizing the documentation sidebar

- To customize the sidebar, use the setting `sidebar_order` in the `docs.php` config.
- When customizing the sidebar, can use the route key, or just the [page identifier](core-concepts#page-identifiers) of the page.

Learn more in the [Documentation Pages](documentation-pages) documentation.

>info Tip: When using subdirectory-based dropdowns, you can set their priority using the directory name as the array key.

#### Automatic navigation menu dropdowns

HydePHP has a neat feature to automatically place pages in dropdowns based on subdirectories.

For pages that can be in the main site menu, ths feature needs to be enabled in the `hyde.php` config file.

```php
// filepath config/hyde.php

'navigation' => [
    'subdirectories' => 'dropdown',
],
```

Now if you create a page called `_pages/about/contact.md` it will automatically be placed in a dropdown called "About".

#### Automatic documentation sidebar grouping

This feature works similarly to the automatic navigation menu dropdowns, but instead place the sidebar items in named groups.
This feature is enabled by default, so you only need to place your pages in subdirectories to have them grouped.

For example: `_docs/getting-started/installation.md` will be placed in a group called "Getting Started".

## Additional Advanced Options

The following configuration options in the `confg/hyde.php` file are intended for advanced users and 
should only be modified if you fully understand their impact. The code examples show the default values.

### `media_extensions`

This option allows you to specify file extensions considered as media files, which will be copied to the output directory. 
To add more extensions, either append them to the existing array or override the entire array.

```php
// filepath config/hyde.php
use \Hyde\Support\Filesystem\MediaFile;

'media_extensions' => array_merge([], MediaFile::EXTENSIONS),
```

### `safe_output_directories`

This setting defines a list of directories deemed safe to empty during the site build process as a safeguard to prevent accidental data loss.
If the site output directory is not in this list, the build command will prompt for confirmation before emptying it. It is preconfigured
with common directories including the default one, but you are free to change this to include any custom directories you may need.

```php
// filepath config/hyde.php
'safe_output_directories' => ['_site', 'docs', 'build'],
```

### `generate_build_manifest`

Determines whether a JSON build manifest with metadata about the build should be generated. Set to `true` to enable.

```php
// filepath config/hyde.php
'generate_build_manifest' => true,
```

### `build_manifest_path`

Specifies the path where the build manifest should be saved, relative to the project root.

```php
// filepath config/hyde.php
'build_manifest_path' => 'app/storage/framework/cache/build-manifest.json',
```

### `hydefront_version` and `hydefront_cdn_url`

These options allow you to specify the HydeFront version and CDN URL when loading `app.css` from the CDN.

Only change these if you know what you're doing as some versions may incompatible with your Hyde version.

```php
// filepath config/hyde.php
use \Hyde\Framework\Services\AssetService;

'hydefront_version' => AssetService::HYDEFRONT_VERSION,
'hydefront_cdn_url' => AssetService::HYDEFRONT_CDN_URL,
```

## Blade Views

Hyde uses the Laravel Blade templating engine. Most parts of the included templates have been extracted into components to be customized easily.
Before editing the views you should familiarize yourself with the [Laravel Blade Documentation](https://laravel.com/docs/10.x/blade).

To edit a default Hyde component you need to publish them first using the `hyde publish:views` command.

```bash
php hyde publish:views
```

The files will then be available in the `resources/views/vendor/hyde` directory.

## Frontend Styles

Hyde is designed to not only serve as a framework but a whole starter kit and comes with a Tailwind starter template
for you to get up and running quickly. If you want to customize these, you are free to do so.
Please see the [Managing Assets](managing-assets) page to learn more.

## Markdown Configuration

Hyde uses [League CommonMark](https://commonmark.thephpleague.com/) for converting Markdown into HTML, and
uses the GitHub Flavored Markdown extension. The Markdown related settings are found in the `config/markdown.php` file.
Below follows an overview of the Markdown configuration options available in Hyde.

### CommonMark Extensions

You can add any extra [CommonMark Extensions](https://commonmark.thephpleague.com/2.3/extensions/overview/),
or change the default ones, using the `extensions` array in the config file. They will then automatically be loaded into
the CommonMark converter environment when being set up by Hyde.

```php
// filepath: config/markdown.php
'extensions' => [
    \League\CommonMark\Extension\GithubFlavoredMarkdownExtension::class,
    \League\CommonMark\Extension\Attributes\AttributesExtension::class,
],
```

Remember that you may need to install any third party extensions through Composer before you can use them.

### CommonMark Configuration

In the same file you can also change the configuration values to be passed to the CommonMark converter environment.
Hyde handles many of the options automatically, but you may want to override some of them and/or add your own.

```php
// filepath: config/markdown.php
'config' => [
    'disallowed_raw_html' => [
        'disallowed_tags' => [],
    ],
],
```

See the [CommonMark Configuration Docs](https://commonmark.thephpleague.com/2.3/configuration/) for the available options.
Any custom options will be merged with the defaults.

### Allow Raw HTML

Since Hyde uses [GitHub Flavored Markdown](https://commonmark.thephpleague.com/2.3/extensions/github-flavored-markdown/),
some HTML tags are stripped out by default. If you want to allow all arbitrary HTML tags, and understand the risks involved,
you can use the `allow_html` setting to enable all HTML tags.

```php
// filepath: config/markdown.php
'allow_html' => true,
```

### Allow Blade Code

HydePHP also allows you to use Blade code in your Markdown files. This is disabled by default, since it allows
arbitrary PHP code specified in Markdown to be executed. It's easy to enable however, using the `enable_blade` setting.

```php
// filepath: config/markdown.php
'enable_blade' => true,
```

See the [Blade in Markdown](advanced-markdown#blade-support) documentation for more information on how to use this feature.

## YAML Configuration

The settings in the `config/hyde.php` file can also be set by using a `hyde.yml` file in the root of your project directory.

Note that YAML settings cannot call any PHP functions, so you can't access helpers like `env()` for environment variables,
nor declare authors or navigation links, as you cannot use facades and objects. But that doesn't stop you from using both
files if you want to. Just keep in mind that any duplicate settings in the YAML file override any made in the PHP file.

Here is an example showing some of the `config/hyde.php` file settings, and how they would be set in the YAML file.

```yaml
# filepath hyde.yml
name: HydePHP
url: "http://localhost"
pretty_urls: false
generate_sitemap: true
rss:
  enabled: true
  filename: feed.xml
  description: HydePHP RSS Feed
language: en
output_directory: _site
```

### Namespaced YAML Configuration

If you are running `v1.2` or higher, you can also use namespaced configuration options in the YAML file.

This allows you to set the settings of **any** configuration file normally found in the `config` directory.

This feature is automatically enabled when you have a `hyde:` entry **first** in your `hyde.yml` file

```yaml
# filepath hyde.yml
hyde:
  name: HydePHP

docs:
  sidebar:
    header: "My Docs"
```

This would set the `name` setting in the `config/hyde.php` file, and the `sidebar.header` setting in the `config/docs.php` file.

Each top level key in the YAML file is treated as a namespace, and the settings are set in the corresponding configuration file.
You can of course use arrays like normal even in namespaced configuration.
