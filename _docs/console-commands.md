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

### The Post Make Command
You can of course create blog posts the old fashioned way by just creating the files yourself, but what's the fun in that?

Using the Make command you will be asked a series of questions which are then used to scaffold a blog post file. It automatically takes care of YAML Front Matter formatting and generates a slug from the supplied title and even adds the current date.

```bash
php hyde make:post
```

> Tip: To overwrite existing files, supply the --force flag (at your own risk of course)


## NPM Commands
The NPM commands are used to compile the frontend CSS assets and to run the realtime compiler.

Make sure you have Node and NPM installed to use these, and if it's the first time running a command, remember to run `npm install` first!

## Commands for the frontend assets
- **`npm run dev`** - Compiles the SASS and Tailwind
- **`npm run prod`** - Compiles the SASS and Tailwind and minifies the output.


## Realtime compiler AKA Watching files for changes

Hyde has a real-time compiler that watches your files for changes and rebuilds the site on the fly.
> Currently, all pages are rebuilt, but in a future update, only the affected files will be rebuilt.

The real-time viewer also uses Browsersync which starts a local web server and automatically refreshes your pages once they are changed. 

**To start the preview run**
```bash
npm run watch
```
A browser page should automatically be opened. If not, just navigate to http://localhost:3000/.
