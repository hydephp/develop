# Console Commands
Hyde is based on [Laravel Zero](https://laravel-zero.com/), which is a micro-framework for console applications.

As such, when you are not writing Markdown posts, most of your time with Hyde will be spent using the CLI.

To help in developing your site we have also included a few scripts in the `package.json`. 

## Hyde Commands
The main place you will interact with Hyde is with the Hyde Console which you access by navigating to your project directory and running the `php hyde` command. If you have ever used the Artisan Console in Laravel you will feel right at home, the Hyde CLI is based on Artisan after all!

Let's take a quick rundown of the most common commands you will use.

You can always run the base command `php hyde` to show the list of commands:
```bash
// torchlight! {"lineNumbers": false}
     __ __        __    ___  __ _____
    / // /_ _____/ /__ / _ \/ // / _ \
   / _  / // / _  / -_) ___/ _  / ___/
  /_//_/\_, /\_,_/\__/_/  /_//_/_/
       /___/

  v0.1.0-pre

  USAGE: hyde <command> [options] [arguments]

  build     Build the static site
  inspire   Display an inspiring quote

  make:post Scaffold a new Markdown blog post file
```

> Tip: You can always add --help to a command to show detailed usage output

### The Build Command

Maybe the most important command is the Build command, which -- you guessed it -- builds your static site!

```bash
php hyde build
```

> If you want to to prettify the output HTML you can add the `--pretty` option. This requires that you have Node and NPM installed as it uses the Prettier NPM module.

#### The Rebuild Command

Using the `php hyde build` command is great and all that, but when you just need to update that one file it gets a little... overkill.

Let's solve that! Use the `php hyde rebuild <file>` command!

In the future it will support an array of files, but for now, the rebuild command will recompile just that file.

### The Post Make Command
You can of course create blog posts the old fashioned way by just creating the files yourself, but what's the fun in that?

Using the Make command you will be asked a series of questions which are then used to scaffold a blog post file. It automatically takes care of YAML Front Matter formatting and generates a slug from the supplied title and even adds the current date.

```bash
php hyde make:post
```

> Tip: To overwrite existing files, supply the --force flag (at your own risk of course)

### The Publish Command
If you are coming from Laravel, you are probably familiar with the Artisan vendor:publish command.

Hyde has a similar command that allows you to publish various pages.

#### Publish Configs
To publish the default configuration files (if you mess something up, or when updating) run the following command. You may need to supply the --force option to overwrite existing files.
```bash
php hyde publish:config [--force]
```

#### Publish a Homepage
Hyde comes with 3 build in homepages. On a fresh install the page 'welcome' is installed. However, you can use the publish command to publish another one. You will probably need to supply the --force option to overwrite existing files.

The available homepages are: 
- *blank*: This is a blank Blade page that simply contains the base layout
- *post feed*: This is the view that this documentation site uses. It contains a listing of the latest posts and was previously the default.
- *welcome*: This is the current welcome page. Unlike the other pages, the styles are defined inline.

When publishing any of these pages they will be saved as index.blade.php in the `_pages` directory which the compiler will use to create the index.html page.

Tip: If you want to have a /posts page you can publish the post feed homepage, rename it to `posts.blade.php` and republish another home page!

#### Publish the Default Views & Components
Since Hyde is based on the Laravel view system the compiler uses Blade views and components. 

Laravel actually registers two locations for the Hyde views: your site's resources/views/vendor directory and the directory source directory in the Framework package.

So, when compiling a site, Laravel will first check if a custom version of the view has been placed in the resources/views/vendor/hyde directory by the developer (you). Then, if the view has not been customized, Laravel will search the Hyde framework view directory. This makes it easy for you to customize / override the package's views.

To publish the views, use
```bash
php hyde publish:views
```
you will then be asked to select which views you want to publish. There are 3 options:
- *components*: These are the reusable components used by the framework
- *layouts*: These are the base layouts used by the framework
- *404 page*: This is a special view, containing a beautiful Blade view from [LaravelCollective](https://github.com/LaravelCollective/errors). When published, it will be compiled into 404.html.

> Note that when a view is updated in the framework you will need to republish the views to get the new changes! You can supply the --force tag to overwrite any existing files.

### The Validate Command
Hyde ships with a very useful command that runs a series of checks to validate your setup and catch any potential issues.

The command is `php hyde validate` and gives an output similar to this
```bash
// torchlight! {"lineNumbers": false}
$ php hyde validate

Running validation tests!

   PASS  CheckForPageConflictsTest
   ✓ check for conflicts between blade and markdown pages

   PASS  CheckThatAnIndexFileExistsTest
   ✓ check that an index file exists

   WARN  CheckThatDocumentationPagesHaveAnIndexPageTest
   ! check that documentation pages have an index page
   → Could not find an index.md file in the _docs directory!

   PASS  CheckThatFrontendAssetsExistTest
   ✓ check that app.css exist
   ✓ check that tailwind.css exist


  Tests:  1 warnings, 4 passed
  Time:   0.31s

All done!
```

## NPM Commands
The NPM commands are used to compile the frontend CSS assets and to run the realtime compiler.

Make sure you have Node and NPM installed to use these, and if it's the first time running a command, remember to run `npm install` first!

If you don't have Node and NPM installed, and you don't want to install them you can download the [prebuilt styles from GitHub](https://github.com/hydephp/hyde/tree/master/_site/media).

## Commands for the frontend assets
- **`npm run dev`** - Compiles the Tailwind
- **`npm run prod`** - Compiles the Tailwind and minifies the output.


## Realtime compiler AKA Watching files for changes

Hyde has a real-time compiler that watches your files for changes and rebuilds the site on the fly.
> Currently, all pages are rebuilt, but in a future update, only the affected files will be rebuilt.

The real-time viewer also uses Browsersync which starts a local web server and automatically refreshes your pages once they are changed. 

**To start the preview run**
```bash
npm run watch
```
A browser page should automatically be opened. If not, just navigate to http://localhost:3000/.
