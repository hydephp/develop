---
label: "Customizing your Site"
priority: 25
---

# Customizing your Site

## Introduction

Hyde favours <a href="https://en.wikipedia.org/wiki/Convention_over_configuration">"Convention over Configuration"</a>
and comes preconfigured with sensible defaults. However, Hyde also strives to be modular and endlessly customizable if you need it. This page guides you through the many options available!

When referencing configuration options, we often use "dot notation" to specify the configuration file. For example, <code>config('site.name')</code> means that we are looking for the <code>name</code> option in the <code>config/site.php</code> file.

If you want to reference these configuration options in your Blade views, or other integrations, please take a look at the [Laravel Documentation](https://laravel.com/docs/9.x/configuration).

### Front Matter or Configuration Files?

In some cases, the same options can be set in the front matter of a page or in a configuration file. Both ways are always documented, and it's up to you to choose which one you prefer. Note that in most cases, if a setting is set in both the front matter and the configuration file, the front matter setting will take precedence.

## Configuration Files Overview

There are a few configuration files available in the `config` directory. All options are documented, so feel free to look through the files and get familiar with the options available to you.

Below are two tables over the different configuration files. Click on a file name to see the default file on GitHub.

### HydePHP Configuration Files

These are the main configuration files for HydePHP and lets you customize the look and feel of your site, as well as the behaviour of HydePHP.

| Config File                                                                                                        | Description                                                                         |
|--------------------------------------------------------------------------------------------------------------------|-------------------------------------------------------------------------------------|
| <a href="https://github.com/hydephp/hyde/blob/master/config/site.php" rel="nofollow noopener">site.php</a>         | Configuration file for the site presentation settings, like site name and base URL. |
| <a href="https://github.com/hydephp/hyde/blob/master/config/hyde.php" rel="nofollow noopener">hyde.php</a>         | HydePHP Framework settings, like what features to enable, and navigation menus.     |
| <a href="https://github.com/hydephp/hyde/blob/master/config/docs.php" rel="nofollow noopener">docs.php</a>         | Options for the HydePHP documentation site generator module.                        |
| <a href="https://github.com/hydephp/hyde/blob/master/config/markdown.php" rel="nofollow noopener">markdown.php</a> | Configure Markdown related services, as well as change the CommonMark extensions.   |
{.align-top}

>info Tip: The values in site.php can also be set in YAML by creating a hyde.yml file in the root of your project. See [#yaml-configuration](#yaml-configuration) for more information.

### Laravel & Package Configuration Files

Since HydePHP is based on Laravel we also have a few configuration files related to them. You probably don't need to edit any of these unless you want to make changes to the application core.

| Config File                                                                                                            | Description                                                             |
|------------------------------------------------------------------------------------------------------------------------|-------------------------------------------------------------------------|
| <a href="https://github.com/hydephp/hyde/blob/master/config/app.php" rel="nofollow noopener">app.php</a>               | Configures the underlying Laravel application.                          |
| <a href="https://github.com/hydephp/hyde/blob/master/config/commands.php" rel="nofollow noopener">commands.php</a>     | Configures the Laravel Zero commands for the HydeCLI.                   |
| <a href="https://github.com/hydephp/hyde/blob/master/config/cache.php" rel="nofollow noopener">cache.php</a>           | Configures the cache driver and cache path locations.                   |
| <a href="https://github.com/hydephp/hyde/blob/master/config/view.php" rel="nofollow noopener">view.php</a>             | Configures the paths for the Blade View compiler.                       |
| <a href="https://github.com/hydephp/hyde/blob/master/config/torchlight.php" rel="nofollow noopener">torchlight.php</a> | Configures settings for the Torchlight syntax highlighting integration. |

{.align-top}


## Configuration Options

While all options are already documented within the files, here are some further explanations of some of the options.

### RSS feed generation

When enabled, an RSS feed with your Markdown blog posts will be generated when you compile your static site.
Note that this requires that a site_url is set!

```php // config/hyde.php
'generate_rss_feed' => true, // Default is true
```

You can customize the output filename using the following:

```php // config/hyde.php
'rss_filename' => 'feed.rss', // Default is feed.xml
```

You can set the RSS channel description using the following:

```php // config/hyde.php
'rss_description' => 'A collection of articles and tutorials from my blog', // Example
```

If an rss_description is not set one is created by appending "RSS Feed" to your site name.


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
    PostAuthor::create(
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
// filepath: resources/_includes/footer.md
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
One of my (the author's) favourite features with Hyde is its automatic navigation menu and documentation sidebar generator.

#### How it works:
The sidebar works by creating a list of all the documentation pages.

The navigation menu is a bit more sophisticated, it adds all the top-level Blade and Markdown pages. It also adds an automatic link to the docs if there is an `index.md` in the `_docs` directory.

#### Reordering Sidebar Items
Sadly, Hyde is not intelligent enough to determine what order items should be in (blame Dr Jekyll for this), so you will probably want to set a custom order.

Reordering items in the documentation sidebar is as easy as can be. In the hyde config, there is an array just for this. When the sidebar is generated it looks through this config array. If a slug is found here it will get priority according to its position in the list. If a page does not exist in the list they get priority 999, which puts them last.

Let's see an example:
```php
// torchlight! {"lineNumbers": false}
// This is the default values in the config. It puts the readme.md first in order.
'documentationPageOrder' => [
    'readme', // This is the first entry, so it gets the priority 0
    'installation', // This gets priority 1
    'getting-started', // And this gets priority 2
    // Any other pages not listed will get priority 999 
]
```


#### Reordering Navigation Menu Items

Hyde makes an effort to organize the menu items in a sensible way. Putting your most important pages first. This of course may not always be how you want, so it's easy to reorder the menu items. Simply override the `navigation.order` array in the Hyde config. The priorities set will determine the order of the menu items. Lower values are higher in the menu. Any pages not listed will get priority 999.

```php
// filepath config/hyde.php
'navigation' => [
    'order' => [
        'index' => 0, // _pages/index.md (or .blade.php)
        'posts' => 10, // _pages/posts.md (or .blade.php)
        'docs/index' => 100, // _docs/index.md
    ]
]
```

You can also set the priority of a page directly in the front matter. This will override any dynamically infered or config defined priority. While this is useful for one-offs, it can make it harder to reorder items later on. It's up to you which method you prefer to use.

```markdown
---
navigation:
    priority: 10
---
```

Note that since Blade pages do not support front matter, this will only work for Markdown pages.

#### Adding Custom Navigation Menu Links

You can easily add custom navigation menu links similar how we add Authors. Simply add a `NavItem` model to the `navigation.custom` array. 

When linking to an external site, you should use the `NavItem::toLink()` method facade. The first two arguments are the destination and label, both required. Third argument is the priority, which is optional.

```php
// filepath config/hyde.php
'navigation' => [
    'custom' => [
        NavItem::toLink('https://github.com/hydephp/hyde', 'GitHub', 200),
    ]
]
```

Simplified, this will then be rendered as follows:

```html
<a href="https://github.com/hydephp/hyde">GitHub</a>
```


#### Excluding Items (Blacklist)

Sometimes, especially if you have a lot of pages, you may want to prevent links from showing up in the main navigation menu. To remove items from being automatically added, simply add the slug to the blacklist. As you can see, the `404` page has already been filled in for you.

Note that we don't specify the page type, since only top level pages are added to the navigation menu (with the exception of the automatic documentation page link, which can be hidden in the config by using `docs/index`).

```php
'navigation' => [
    'exclude' => [
        '404'
    ]
]
```

You can also specify that a page should be excluded by setting the page front matter. Note that since Blade pages do not support front matter, this will only work for Markdown pages.

```markdown
---
navigation:
    hidden: true
---
```

#### Changing the menu item labels

Hyde makes a few attempts to find a suitable label for the navigation menu items to automatically create helpful titles. You can override the label using the `navigation.label` front matter property.

From the Hyde config you can also override the label of navigation links using the by mapping the route key (identifier/slug relative to the site root) to the desired title. Note that the front matter property will take precedence over the config property.

    
```php
// filepath config/hyde.php
'navigation' => [
    'labels' => [
        'index' => 'Start',
        'docs/index' => 'Documentation',
    ]
]
```


## Blade Views
Hyde uses the Laravel templating system called Blade. Most parts have been extracted into components to be customized easily.

> Before editing Blade views you should familiarize yourself with how they work in the official documentation https://laravel.com/docs/9.x/blade.

To edit the default component you need to publish them first using the `hyde publish:views` command.

The files will then be available in the `resources/views/vendor/hyde` directory.

## Frontend Styles
Hyde is designed to not only serve as a framework but a whole starter kit and comes with a Tailwind starter template for you to get up and running quickly. If you want to customize these, you are free to do so. Please see the [Managing Assets](managing-assets) page to learn more.


## CommonMark environment

Hyde uses [League CommonMark](https://commonmark.thephpleague.com/) for converting Markdown into HTML.

Hyde ships with the GitHub Flavored Markdown extension, and 
the Torchlight extension is enabled automatically when needed.

You can add extra CommonMark extensions, or change the default ones, in the `config/markdown.php` file.

```php
'extensions' => [
	\League\CommonMark\Extension\GithubFlavoredMarkdownExtension::class,
	\League\CommonMark\Extension\Attributes\AttributesExtension::class,
	\League\CommonMark\Extension\DisallowedRawHtml\DisallowedRawHtmlExtension::class,
],
```

In the same file you can also change the config to be passed to the CommonMark environment.

```php
'config' => [
	'disallowed_raw_html' => [
		'disallowed_tags' => [],
	],
],
```

## YAML Configuration

As a relatively new and experimental feature, the settings in the config/site.php can also be overridden by creating
a hyde.yml file in the root of your project directory. Note that these cannot reference environment variables, 
and their values override any made in the PHP config.

Here is an example hyde.yml file matching the default site.yml:

```yaml
# filepath hyde.yml
name: HydePHP
url: http://localhost
pretty_urls: false
generate_sitemap: true
generate_rss_feed: true
rss_filename: feed.xml
# rss_description:
language: en
output_directory: _site
```
