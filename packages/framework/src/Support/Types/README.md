# Custom HydePHP Types

## Preface

HydePHP adds some custom types to PHP. These types are used to make the code more readable and to make the code more type safe.

If you are simply using HydePHP to create sites, you don't need to worry about these.
However, if you are interested in contributing to the framework, you might find the information here useful.

### The Types

#### RouteKey

Route keys are the core of Hyde's routing system.

In short, the route key is the URL path relative to the site root.

For example, if the compiled page will be saved to _site/docs/index.html, then route key will be `docs/index`. 
Route keys are used to identify pages, similar to how named routes work in Laravel. Duplicate route keys will be ignored.

```php
// Source file: _pages/index.md/index.blade.php
// Compiled file: _site/index.html
Route::get('index')

// Source file: _posts/my-post.md
// Compiled file: _site/posts/my-post.html
Route::get('posts/my-post')

// Source file: _docs/readme.md
// Compiled file: _site/docs/readme.html
Route::get('docs/readme')
```