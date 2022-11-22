---
priority: 20
---

# Managing and Compiling Assets

## Introduction

Managing and compiling assets is a very common task in web development. Unfortunately, it's rarely fun. 

With hyde, **you don't have to do it**, in fact, you can skip this entire page if you are happy with how it is.
But as always with Hyde, you can customize everything if you want to.

Hyde ships with a complete frontend using Blade views, TailwindCSS styles, and Alpine.js interactions. Some extra custom styles are made in the HydeFront package, which is pre-installed and bundled in the pre-configured Laravel Mix.

To get you started quickly, all the styles are already compiled minified into `_media/app.css`, which will be copied to the `_site/media/app.css` directory when you run `php hyde build`.

## Some extra information, and answers to possible questions

### Do I have to use NPM to use Hyde?
No. NPM is optional as all the compiled styles you need are already installed. You only need NPM if you want to compile your own styles.

### When do I need to compile assets?

#### When customizing
If you want to customize the Tailwind settings or add custom styles, you will need to take care of compiling the styles yourself.

#### When adding new classes
The `_media/app.css` file that comes with Hyde contains TailwindCSS for all classes that are used in the default Blade views, as well as the HydeFront custom styles.

If you customize the Blade views and add new classes, or if you add new classes in Blade-based pages, you may need to compile the assets yourself to get the new styles.

If you stick to using Markdown based pages, you don't need to compile anything.

## How are assets stored and managed?

Currently, the frontend assets are separated into three places.

The `resources/assets` contains **source** files, meaning files that will be compiled into something else. Here you will find the `app.css` file that bootstraps the TailwindCSS styles. This file is also an excellent place to add your custom styles. It is also where we import HydeFront.

The `_media` folder contains **compiled** (and usually minified) files. When Hyde compiles your static site, all asset files here will get copied as they are into the `_site/media` folder.

The `_site/media` folder contains the files that are served to the user.

### What is the difference between `_media` and `_site/media`?
It may seem weird to have two folders for storing the compiled assets, but it is quite useful.

The `_site` directory is intended to be excluded from version control while the `_media` folder is included in the version control, though you may choose to exclude the compiled files from the `_media` folder if you want to.

You are of course free to modify this behavior by editing the `webpack.mix.js` file.

## How do I compile assets?

First, make sure that you have installed all the NodeJS dependencies using `npm install`.
Then run `npm run dev` to compile the assets. If you want to compile the assets for production, run `npm run prod`.
You can also run `npm run watch` to watch for changes in the source files and recompile the assets automatically.

### How does it work?

Hyde uses [Laravel Mix](https://laravel-mix.com/) (which is a wrapper for [webpack](https://webpack.js.org/)) to compile the assets.

When running the `npm run dev/prod` command, the following happens:

1. Laravel Mix will compile the `resources/assets/app.css` file into `_media/app.css` using PostCSS with TailwindCSS and AutoPrefixer.
2. Mix then copies the `_media` folder into `_site/media`, this is so that they are automatically accessible to your site without having to rerun `php hyde build`, making blend perfectly with the realtime compiler (`php hyde serve`).


## Telling Hyde where to find assets

### Customizing the Blade templates

To make it really easy to customize asset loading, the styles and scripts are loaded in dedicated Blade components.

- Styles are loaded in `hyde::layouts.styles`
- Scripts are loaded in `hyde::layouts.scripts`

To customize them, run the following command:

```bash
php hyde publish:views layouts
```

Then edit the files found in `resources/views/vendor/hyde/layouts` directory of your project.

### You might not even need to do anything!

For the absolute majority of the cases, you don't need to mess with these files. Hyde will automatically load the app.css file when it exists in the `_media` directory.

#### Loading from CDN
If you want to load the same pre-compiled file included with Hyde but from a CDN, you can set `load_app_styles_from_cdn` to `true` in the `config/hyde.php` file. While you lose the ability to customize it, your styles will be automatically updated when needed.

### Using the TailwindCSS Play CDN

If you want to use the [TailwindCSS Play CDN](https://tailwindcss.com/docs/installation/play-cdn), you can set `use_play_cdn` to `true` in the `config/hyde.php` file.
This will in addition to loading the standard app.css file also add a script tag to load the TailwindCSS Play CDN.
What's even better is that Hyde will also inject the contents of the included `tailwind.config.js` file into the script tag, so the Play CDN styles match the ones created by Laravel Mix.
This also means you can tinker around with the TailwindCSS settings without having to compile anything.

>warn Note that the Play CDN is not meant for production use, so enabling it will add a warning to the web console.

## Managing images
As mentioned above, assets stored in the _media folder are automatically copied to the _site/media folder,
making it the recommended place to store images. You can then easily reference them in your Markdown files.

### Referencing images

The recommended way to reference images are with relative paths as this offers the most compatibility,
allowing you to browse the site both locally on your filesystem and on the web when serving from a subdirectory.

>warning Note: The path is relative to the <b>compiled</b> file in the site output

The path to use depends on the location of the page. Note the subtle difference in the path prefix.

- If you are in a **Blog Post or Documentation Page**, use `../media/image.png`
- If in a **Markdown Page or Blade Page**, use `media/image.png`
- While not recommended, you can also use absolute paths: `/media/image.png`

#### Making images accessible

To improve accessibility, you should always add an `alt` text. Here is a full example for an image in a blog post:

```markdown
![Image Alt](../media/image.png "Image Title") # Note the relative path
```

### Setting a featured image for blog posts

Hyde offers great support for creating data-rich and accessible featured images for blog posts.

You can read more about this in the [creating blog posts page](blog-posts#image).

