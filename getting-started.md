# Getting Started
Now that you have installed Hyde, let's get to using it!

> If you have not already, now may be good to familiarize yourself with the [Directory Structure](directory-structure.html).

## Creating content

Hyde has 3 types of content generation (though you are free to add more, submit a PR!). You can think of them as modules if you want.

- Hyde Posts - For the blogging system
- Hyde Docs - For generating documentation pages
- Hyde Pages - For generating both simple Markdown pages and advanced Blade ones

Let's jump in and take a closer look at each of them!

### Hyde Posts
This is the Blogging module. Blog Posts, or simply Posts, are Markdown files stored in the `_posts` directory.

#### Creating posts
Posts can be created in one of two ways:
1. You can create them manually by creating the files, or
2. you can use the ´php hyde make:post´ command which automatically fills in the front matter for you.

> See the tutorial for further instructions on how to use the make:post command.

In both cases, the markdown file should use the kebab-case format and end in .md. When building the static site, the post will retain the filename slug but end in .html instead of .md.

For example:
`_posts/hello-world.md` will become `_site/posts/hello-world.html`

After creating your post, run `php hyde build` to build your site! You should also look at the section dedicated to building the site if you have not already.

#### About Front Matter
These posts use a YAML syntax called "Front Matter" which you may be familiar with from frameworks like Jekyll.

Each post should have a front-matter section before the content. A front matter section begins and ends with rows in the markdown file that consists of three dashes (`---`). Between these lines, you place key-value pairs of data which are shown in the frontend.

Only the `title` is required, though you are encouraged to add any number of the following supported attributes as they are all displayed in the front end.

**Example of a front-matter section**
```yaml
---
title: The title is the only value that is required
description: A short description used in previews and SEO
category: arbitrary-category-that-a,
author: @TheMrHyde
date: YYYY-MM-DD 16:00
---

# Actual Markdown content is placed here
```

> At the moment no nested attributes are supported. The category value does not yet contain much functionality and is safe to omit.

> Masterclass: you can add arbitrary front matter key-value-pairs and access them using `$post->matter['foo']` in a Blade view

#### Tutorial

For a full tutorial see https://hydephp.github.io/posts/creating-a-static-html-post-using-hydephp.html

<!-- 
#### Deep-dive
> Deepdives take a closer look into how a feature works behind the scenes. While not required to know it can help to understand the "magic" behind Hyde. -->

### Hyde Docs 

The Hyde Docs is based on Laradocgen and _automagically_ turns Markdown pages into documentation pages. They are what powers this documentation site!

Creating documentation pages is a piece of cake. Create a file following the format of kebab-case-version-of-the-title.md in the _docs directory. Put any content you want in it, and run the build command.

The sidebar will like magic be populated with all the documentation pages. The page titles in the sidebar are generated from the filename, and the HTML page title is inferred from the first H1 tag.

> **Pro tip 1:** Enable the Torchlight extension to get the beautiful syntax highlighting used here!

> **Pro tip 2:** You can specify the output directory for documentation pages in the Hyde config. This site uses that feature to save the pages in the 'master' directory for easy version support! 

### Hyde Pages using Markdown

Hyde Markdown Pages are perfect for simple content-driven pages. Some examples of this may be "About Us" pages, or legal pages such as "Terms of Service" and "Privacy Policy".

The Markdown pages work similarly to Documentation pages but use a simple Blade layout, putting the focus on your content.
To create a Markdown page, all you need to do is create a file ending in .md in the _pages directory. 

You can use front matter to set the page title,
```markdown
---
title: Hello World!
---
```
If you don't use front matter, the page title is automatically inferred from the first # H1 heading.

You can scaffold Markdown pages using
```bash
php hyde make:page "Page Name"
```

### Hyde Pages using Blade

If you want full control over a static page you can create blade views in the pages directory `_pages`, and they will be compiled into static HTML.

Currently, only top-level pages are supported. The filename of the generated file is based on the view filename.
For example, `_pages\custom-page.blade.php` gets saved as `_site\custom-page.html`.

You can scaffold Blade pages using the make:page command to automatically create the file based on the default layout.
```bash
php hyde make:page "Page Name" --type=blade
```

**⚠ Warning:**
Blade pages take precedence over Markdown pages! Do not use duplicate slugs.
For example, if you have both an `about.md` and `about.blade.php`, only the Blade page will be compiled. 

**Using the default layout**
If you want to match the styles of the rest of your app you can extend the default layout.
```blade
@extends('hyde::layouts.app')
@section('content')

// Place content here

@endsection
```

You can reference any Hyde components, or add your own templates!
You can also set the page title using
```blade
@php($title = "My Custom Title")
```

### Adding Images

All media files in the _media directory will get copied to the _site/media directory upon build. To reference an image in your Markdown, use the following syntax
To reference an image in your Markdown, use the following syntax
```markdown
![Image Alt](../media/image.png "Image Title") # Note the relative path
```

Since most images are probably going to be in blog posts or documentation pages you need to prepend the `../` before the "media". However, if you are referencing the image on a Markdown page you should use `media/image.png` for the path.

> Nested media directories are not yet supported.


## Building the static site

To compile the site into static HTML all you have to do is execute the Hyde build command.
```bash
php hyde build
```

Your site will then be saved in the _site directory, which you can then upload to your static web host.
All links use relative paths, so you can deploy to a subdirectory without any problems which also makes the site work great when browsing the HTML files locally even without a web server.

If it is the first time building the site or if you have updated the source SCSS you should also run `npm install && npm run dev` to build the frontend assets.

## Frontend stylesheets and scripts

Hyde is designed to not only serve as a framework but a whole starter kit. Hyde comes with a Tailwind starter template for you to get up and running quickly.

### The default frontend resource files are as follows:

- **App.css**
This file is mostly blank and only contains the TailwindCSS imports. If you want to add your own styles, this is a great place. Running `npm run dev/prod` will compile the Tailwind styles and any custom ones you define into `./_site/media/app.css`.

- **Hyde.css**
This file contains the base styles and should be loaded after App.css as it contains some Tailwind tweaks.

- **Hyde.js**
This file contains basic scripts to make the navigation menu and sidebars interactive.

You can customize all of them to your liking, however, if you edit the Hyde.css or Hyde.js you may need to merge them manually when updating to a version if they have been changed. All three files will get copied into the `./_site/media/` directory when running the build command.

### Updating to the latest version
When updating Hyde, you may need to update the frontend resource files. You can do this using the following command:
```bash
php hyde update:resources
```

Note that this command will overwrite existing files!
The following files in the `resources/frontend/` directory will be overwritten:
- hyde.css
- hyde.js
- app.css
Back up your content before running the command! (use Git so you can merge any conflicts)

