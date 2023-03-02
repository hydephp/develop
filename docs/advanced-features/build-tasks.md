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

You can also set the message, and an optional `then()` method to run after the main task has been executed. The then method is great if you want to display a status message.

```php
<?php

namespace App\Actions;

use Hyde\Framework\Features\BuildTasks\BuildTask;

class ExampleTask extends BuildTask
{
    public static string $message = 'Say hello';

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
'build_tasks' => [
    \App\Actions\SimpleTask::class,
    \App\Actions\ExampleTask::class,
],
```

If you are developing an extension, I recommend you do this in the `boot` method of a service provider so that it can be loaded automatically. Do this by adding the fully qualified class name to the `BuildTaskService::$postBuildTasks` array.

Hyde can also autoload them if you store the files in the `app/Actions` directory and the names end in `BuildTask.php`. For example `app/Actions/ExampleBuildTask.php`.
