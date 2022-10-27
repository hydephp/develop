---
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

### A note on file paths

When Hyde references files, especially when passing filenames between components, the file path is almost always relative to the root of the project. When an absolute path is required, the path is resolved through the `Hyde::path()` helper. Specifying absolute paths yourself will likely lead to unforeseen problems.

## Customizing source directories 🧪

>warning This may cause integrations such as the realtime compiler to break. You'll also likely need to update route key names in your templates.

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

### Setting an absolute path

Since Hyde v0.64.0-beta, the site output directory will always be resolved within the project root. If you want to compile the site to an absolute path outside your project, it's instead recommended that you use a build task to copy the files to the desired location automatically after the site has been compiled. 

## Adding custom post-build tasks

These tasks are code that is executed automatically after the site has been built using the `php hyde build` command. The built-in features in Hyde like sitemap generation and RSS feeds are created using tasks like these.

Maybe you want to create your own, to for example upload the site to FTP or copy the files to a public directory? It's easy to do, here's how!

### Minimal example

Here is a minimal example to get you started. For all these examples we assume you put the file in the `App/Actions` directory, but you can put them anywhere.

```php
class SimpleTask extends BuildTask
{
    public function run(): void
    {
        $this->info('Hello World!');
    }
}
```

This will then output the following, where you can see that some extra output, including execution time tracking is added for us. We can of course customize this if we want, as you can see in the next example.

<pre>
<small style="color: gray">$ php hyde build</small>
  <span style="color: gold">Generic build task...</span> Hello World! <span style="color: gray">Done in 0.26ms</span>
</pre>


### Full example

You can also set the description, and an optional `then()` method to run after the main task has been executed. The then method is great if you want to display a status message.

```php
<?php

namespace App\Actions;

use Hyde\Framework\Features\BuildTasks\BuildTask;

class ExampleTask extends BuildTask
{
    public static string $description = 'Say hello';

    public function run(): void
    {
        $this->info('Hello World!');
    }

    public function then(): void
    {
		$this->line('Goodbye World!');
    }
}
```

<pre>
<small style="color: gray">$ php hyde build</small>
  <span style="color: gold">Say hello...</span> <span style="color: green">Hello World!</span>
  Goodbye World!
</pre>


### Registering the tasks

There are a few ways to register these tasks so Hyde can find them. There is a convenient place to do this, which is in the main configuration file, `config/hyde.php`.

```php
// filepath config/hyde.php
'post_build_tasks' => [
    \App\Actions\SimpleTask::class,
    \App\Actions\ExampleTask::class,
],
```

If you are developing an extension, I recommend you do this in the `boot` method of a service provider so that it can be loaded automatically. Do this by adding the fully qualified class name to the `BuildTaskService::$postBuildTasks` array.

Hyde can also autoload them if you store the files in the `app/Actions` directory and the names end in `BuildTask.php`. For example `app/Actions/ExampleBuildTask.php`.
