# Custom Build Tasks

## Introduction

The Build Task API offers a simple way to hook into the build process.
The build tasks are very powerful and allow for limitless customizability.

The built-in Hyde features like sitemap generation and RSS feeds are created using tasks like these.
Maybe you want to create your own, to for example upload the site to FTP or copy the files to a public directory?
You can also overload the built-in tasks to customize them to your needs.


## Good to know before you start

### Types of tasks

There are two types, PreBuildTasks and PostBuildTasks. As the names suggest, PreBuildTasks are executed before the site is built, and PostBuildTasks are executed after the site is built.

To choose which type of task you want to create, you extend either the `PreBuildTask` or `PostBuildTask` class.
Both of these have the exact same helpers and API available, so the only difference between them is when they are executed. The classes are otherwise identical.

### About these examples

For most of these examples we will focus on the PostBuildTasks as they are the most common.

For all these examples we assume you put the file in the `App/Actions` directory, but you can put them anywhere.

### Interacting with output

In a way, build tasks are like micro-commands, as they can interact directly with the build commands I/O. Please take a look at the [Laravel Console Documentation](https://laravel.com/docs/10.x/artisan#command-io) for the full list of available methods.

In addition, there are some extra helpers available in the base BuildTask class that allow you to fluently format output to the console, which you will see in the examples below.


## Creating build tasks

### Minimal example

Here is a minimal example to give you an idea of what we are working with.

```php
class SimpleBuildTask extends PostBuildTask
{
    public function handle(): void
    {
        //
    }
}
```

As you can see, at their core, build tasks are simple classes containing a `handle()` method,
which as I'm sure you have guessed, is the method that is executed when the task is run by the build command.

If you want the task to run before the build, you would extend the `PreBuildTask` class instead.

#### Automatic output

When running the build command, you will see the following output added after the build is complete.

<pre>
 <span style="color: gold">Generic build task...</span> <span style="color: gray">Done in 0.26ms</span>
</pre>

As you can see, some extra output including execution time tracking is added for us. We can of course customize all of this if we want, as you will learn a bit further down.

### Full example

Here is a full example, with all the namespaces included, as well as the most common fluent output helpers.

```php
<?php

namespace App\Actions;

use Hyde\Framework\Features\BuildTasks\PostBuildTask;

class ExampleTask extends PostBuildTask
{
    public static string $message = 'Say hello';

    public function handle(): void
    {
        $this->info('Hello World!');
    }

    public function printFinishMessage(): void
    {
        $this->line('Goodbye World!');
    }
}
```

You can see a full API reference further below. But in short, the `$message` property is the message that runs before the task is executed, and the `printFinishMessage()` method is the message that runs after the task is executed.

Running this task will produce the following output:

<pre>
<small style="color: gray">$ php hyde build</small>
  <span style="color: gold">Say hello...</span> <span style="color: green">Hello World!</span>
  Goodbye World!
</pre>

As you can see, there is no execution time tracking here, since we overrode the `printFinishMessage()` method that normally prints this. You can of course call the `withExecutionTime()` method to add this back in. See more in the API reference below.


## Registering the tasks

There are a few ways to register these tasks so Hyde can find them.

They are shown here in order of presumed convenience, but you are free to choose whichever you prefer. The latter options are more suited for extension developers.

### Autodiscovery registration

The easiest way to register build tasks, is to not do it. Just let Hyde do it for you!

Any classes that end in `BuildTask.php` that are stored in `app/Actions`  will be autoloaded and registered to run automatically.

For example: `app/Actions/ExampleBuildTask.php`.

### Config file registration

If you want, you can also register build tasks of any namespace in the convenient `build_tasks` array which is in the main configuration file, `config/hyde.php`.

```php
// filepath config/hyde.php
'build_tasks' => [
    \App\Actions\SimpleTask::class,
    \MyPackage\Tasks\MyBuildTask::class,
],
```

### Programmatic registration

>info This option assumes you are familiar with Laravel's service container and service providers.

If you are developing an extension, you can either instruct users register your tasks with the config option above,
or you can register the extensions programmatically, I recommend you do this in the `boot` method of a service provider.

The build tasks are registered in an internal array of the `BuildService` class, which is bound as a singleton in the underlying Laravel service container.
To actually register your task, provide the fully qualified class name of the task to the `BuildTaskService::registerTask()` method.

Here is an example of how to do this using in a service provider. Though you could technically do it anywhere using the `app()` helper, just as long as it's done early enough in the application lifecycle, so it's registered before the build command is executed.

```php
class MyServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->app->make(\Hyde\Framework\Services\BuildTaskService::class)
            ->registerTask(\MyPackage\Tasks\MyBuildTask::class);
    }
}
```
