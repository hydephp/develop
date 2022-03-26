---
title: Creating a new Hyde site from scratch
description: This blog post will guide you through creating a new Hyde site, while also showcasing some neat features!
category: tutorials
author: Caen
date: 2022-03-25 20:49
---

<p class="lead">
This blog post will guide you through creating a new Hyde site, while also showcasing some neat features!
</p>

## Prerequisites
This guide assumes you have Composer and NPM installed. You also need PHP 8 or higher.
Previous command-line experience won't hurt either since we will be using the CLI a lot! I'm using Windows PowerShell here, but you can follow along with Bash as well. 

This guide will mostly be example-driven. If you have any questions, please send me a tweet! I'm [@StressedDev](https://twitter.com/StressedDev)

## Creating a new project

Creating a site with Hyde is easy, especially when using Composer!

```bash
composer create-project hyde/hyde tutorial-demo
```

CD into the created directory using `cd tutorial demo`

Right away you can run the build command

```bash
php hyde build
```

and open the generated HTML page that is stored as `index.html` in the `_site` directory. Let's take a look!

![Screenshot of welcome page](../media/screely-1648242017926-min.png)

Amazing! We probably don't want to keep this welcome screen though. Let's create a blog!

## Setting up the blog homepage

Hyde comes with a built-in blog module. First, let's change our homepage!

Hyde comes with a few different options to use as the index.html. The one we have right now, the default, is called 'welcome'.

We can change our homepage using the Hyde command which will present us with a few options. We'll select the one named 'post feed'. Since we already have a homepage we need to add the --force flag to allow it to be overwritten. This is a safeguard in case you have changed the file yourself.
```bash
php hyde publish:homepage --force
```

We will be asked if we want to rebuild the site, let's hit 'yes' and take a look!

![Screenshot of welcome page](../media/screely-1648242979072-min.png)

Cool! Though it looks a bit empty. Let's create a post!

## Creating a blog post
Blog posts are based on Markdown files with metadata (post information) specified in a special YAML called Front Matter.

We can of course create the file manually but that is so old fashioned. Instead, let's use the interactive command to scaffold it for us!

The command we are using is the `php hyde make:post` command, which gives asks us for input and then generates the boring stuff for us.

Here is what the output looks like:
![Screenshot of command output](../media/Screenshot 2022-03-25 222236-min.png)

Let's take a look at the file that was created for us! As you can see it is stored in the `_posts` directory. A slug was automatically created from the title and the date was automatically parsed from the current time.

```markdown
---
title: Hello World!
description: This will show up in the article excerpt and SEO meta tags
category: demo
author: Caen
date: 2022-03-25 21:20
---

## Write something awesome.

```

Let's rebuild the site and take a look!

As you can see, the excerpt automatically shows up in the feed on the homepage! I also added some extra text in the Markdown section.
![Screenshot of generated homepage and blog post](../media/mockup-blogpostexample-min.png)

## Creating a custom Markdown page

Now that we have a nice blog post, why not try our hand at creating an about us page?

Hyde allows the creation of simple Markdown based pages. Markdown files in the `_pages` directory are automatically compiled into static HTML.

Let's try it!

I'll use the command line to create the file. We need to add a block of front matter with the page title.

```bash
touch _pages/about-us.md
```

and add some content

```markdown
---
title: About Us
---

# This is an example of a Markdown-based static HTML page built with Hyde

Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod
tempor incididunt ut labore et dolore magna aliqua...
```

And rebuild the site:

![Screenshot of generated Markdown page](../media/screely-1648247108115-min.png)

If you have a keen eye, you will see that a link to the page has been added to the navigation menu!

This is all well and good, but it's getting a bit tedious to have to go back to the terminal and rerun the build command all the time. Wouldn't it be nice if Hyde could automatically rebuild the site for us? Oh, it can? Yup, Hyde has a real-time compiler that we have to try out!

## Auto-building on the fly with the real-time compiler

The real-time compiler -- let's call it "RC" because A: it's shorter, and B: it sounds cool -- uses a few NPM modules, so first we should install the dependencies. Make sure you have Node and NPM installed!

```bash
npm install
```

Next, we run the command to start the RC:

```bash
npm run watch
```

A window in your default browser should now open up automatically. If not, just navigate to http://localhost:3000/!

Let's go back to our blog post and change the text. Once you hit save the site will automatically rebuild and the browser window will be updated without you even having to reload! It's a bit hard to illustrate this behaviour in text, so be sure to try it out yourself!

## Let's create a Blade Page!

I'm getting a bit bored of the Markdown About Us page we created earlier. Let's swap it out with a page based on a Blade view!

Creating Blade pages is similar to creating Markdown pages, but instead of saving a Markdown file in the `_posts` directory we create a file ending in .blade.php in the `resources/views/pages` directory.

Let's create the file using the CLI.
```bash
touch resources/views/pages/about-us.blade.php
```

> By the way, this is the same directory where we have the index.blade.php file which is the homepage file.

Blade files take precedence over Markdown files as they are compiled later in the build process, so if you go to the about-us.html you should see a blank page.

When writing Blade pages you have a few options since we can utilize the full power Laravel brings us. If you want you can skip using Blade and just write pure HTML (like the welcome page), or you can use Blade templates and components (like the post feed page). I'm going to extend the default layout so Hyde can automatically inject the proper stylesheets etc. Feel free to use the code below to kickstart creating your custom Blade page!

```blade
@extends('hyde::layouts.app')
@section('content')
@php($title = "My Custom Title") {{-- This is optional, but lets us set the page title --}}

// Place content here
 
@endsection
```

I'll be uploading this tutorial repo to GitHub so you can take a look at the Blade page. I'm renaming it to blade.blade.php though.

## Document the documentation!

Documentation is important! If a feature is not documented, it does not exist! "But writing documentation is soooo boring" you might say, and to that, I say, "not with Hyde!"

You heard me! Writing documentation is fun again! Best of all? It is dead simple.

The Hyde Docgen module is based on Laradocgen and automagically turns Markdown pages into documentation pages. They are what powers the Hyde documentation site!

Creating documentation pages are a piece of cake. We start by creating a file following the format of kebab-case-version-of-the-title.md in the `_docs` directory. 

```bash
echo "# Hello World!" > _docs/hello-world.md
```

We can now take a look at http://localhost:3000/docs/hello-world.html where we should have a nice page waiting for us!

The sidebar will like magic be populated with all the documentation pages.
The page titles in the sidebar are generated from the filename, and the HTML page title is inferred from the first H1 tag.

Wanna know another cool thing? If you create a file in the `_docs` directory named index.md or readme.md a link labelled docs linking to it will be added automatically to the main navigation menu!

And if you create an index.md file here, which is recommended, it will be not be shown as a link in the sidebar but instead, the header in the sidebar will link to it.

> In the Hyde documentation site I am using the project readme as the index.md file

## Adding Torchlight Syntax Highlighting

Our documentation is cool and all that, but I want to add lots of code examples, but they look so boring without syntax highlighting...

![Screenshot of the documentation page with no syntax highlighting](../media/screely-1648303538589-min.png)

Hyde has built-in support for one of my favourite packages, Torchlight, which is free for open source and non-commercial projects and requires an attribution link, which Hyde injects automatically on pages that use Torchlight!

To get started you need an API token from Torchlight which you can get for free on their website: https://torchlight.dev/

Once you have the token you need to set it in your dotenv file. If you don't have one you can copy the example file supplied with the project using this command (or by renaming/copying it manually)

```bash
cp .env.example .env
```

Then in the .env file, add your API token like so:
```bash
TORCHLIGHT_TOKEN=torch_abcdefg123
```

Adding the token makes Hyde automatically enable the Torchlight extension.

Now when we run the build command you will notice it takes a bit longer, especially the first time, but no sweat because we got ourselves some beautiful code blocks!

![Screenshot of the documentation page with syntax highlighting](../media/screely-1648307127051-min.png)

## Conclusion

I think that brings this tutorial to a close. Tweet any questions you have at me on [@StressedDev](https://twitter.com/StressedDev)

Please share this post if you found it useful, and let me know if you want a part 2 where I dig in deeper and show how to customize your new site!

I'll be uploading the code created in this tutorial to https://github.com/caendesilva/demo-hyde-tutorial-example-project