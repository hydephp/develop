## Automatic Routing

>info This covers an intermediate topic which is not required for basic usage, but is useful if you want to use the framework to design custom Blade templates.

### High-level overview

If you've ever worked in an MVC framework, you are probably familiar with the concept of routing.
And you are probably also familiar with how boring and tedious it can be. Thankfully, Hyde takes the pain out of routing
through the Hyde Autodiscovery process.

Internally, when booting the HydeCLI application, Hyde will automatically discover all the content files in the source
directories, and create a route index for all of them. This index works as a two-way link between source files and compiled files.

Don't worry if this sounds complex, as the key takeaway is that the index is created and maintained automatically.
Nevertheless, the routing system provides several helpers that you can optionally use in your Blade views to
automatically resolve relative links and other useful features.

You can see all the routes and their corresponding source files by running the `hyde route:list` command.

```bash
php hyde route:list
```

### Accessing routes

Each route in your site is represented by a Route object. It's very easy to get a Route object instance from the Router's index.
There are a few ways to do this, but most commonly you'll use the Routes facade's `get()` method where you provide a route key,
and it will return the Route object. The route key is generally `<page-output-directory/page-identifier>`. Here are some examples:

```php
// Source file: _pages/index.md/index.blade.php
// Compiled file: _site/index.html
Routes::get('index')

// Source file: _posts/my-post.md
// Compiled file: _site/posts/my-post.html
Routes::get('posts/my-post')

// Source file: _docs/readme.md
// Compiled file: _site/docs/readme.html
Routes::get('docs/readme')
```

### Using the `x-link` component

When designing Blade layouts it can be useful to use the `x-link` component to automatically resolve relative links.

You can of course, use it just like a normal anchor tag like so:

```blade
<x-link href="index.html">Home</x-link>
```

But where it really shines is when you supply a route. This will then resolve the proper relative link, and format it to use pretty URLs if your site is configured to use them.

```blade
<x-link :href="Routes::get('index')">Home</x-link>
```

You can of course, also supply extra attributes like classes:

```blade
<x-link :href="Routes::get('index')" class="btn btn-primary">Home</x-link>
```
