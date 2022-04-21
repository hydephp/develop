# Directory Structure

To take full advantage of the framework, it may first be good to familiarize ourselves with the directory structure.

## Tree Overview
```
// torchlight! {"lineNumbers": false}
├── _docs              
├── _media              
├── _pages             
├── _posts             
├── _site              
├── config             
├── resources
│   └── frontend
│   └── views          
│       ├── components 
│       └── layouts      
```

## Directory Explanation 
It may be helpful to separate the two types of directories we have.

First, we have the content directories, these are prefixed with an underscore (`_`).

Then we have the resource directories, they contain the HTML (Blade) templates and similar. If you are just getting started you may not need to dig into the second category, but they are available for you to play around with! 

Let's take a look!

### Content Directories

#### `_posts` 
This is where the blog post files are stored. Files here support YAML front matter.

You can scaffold posts using the `php hyde make:post` command with automatically creates the front matter based on your input selections.

A _posts directory filled with posts may look similar to this.
```
// torchlight! {"lineNumbers": false}
_posts
├── hello-world.md
├── my-first-post.md
├── diary-of-a-volunteer.md
├── benefits-of-milkshakes.md
└── a-fifth-longer-post-here.md
```

**Limitations:** Currently only top-level posts are supported. Files should use kebab-case format and must also end in .md and contain front matter to be recognized.

#### `_docs` 
Hyde includes a spiritual successor of [Laradocgen](https://github.com/caendesilva/laradocgen)

All you need to do to create a documentation page is to place a Markdown file in this directory and run the build command.
The sidebar will automatically be populated with the page title which is derived from the first H1 (# Title) tag.

This documentation page is built with HydeDocs, and you can take a look at the source code on https://github.com/hydephp/docs which also serves this site through GitHub Pages.

**Limitations:** Currently only top-level posts are supported. Soon (hopefully) you will be able to put files in subdirectories, or specify a parent, to create a sidebar with categories.

Files should use kebab-case format and must also end in .md and contain front matter to be recognized.

#### `_pages` 
You can also place Markdown and Blade files here and they will be compiled into simple top-level pages.

Markdown is perfect for about pages, or terms of service policy pages!

Blade pages are excellent for when you want full control over the layout of your site. The default homepages are built with Blade pages.

**Limitations:** Only top-level pages are supported. Files should use kebab-case format and must also end in .md and contain front matter to be recognized.

> Make sure the slug does not conflict with a custom Blade page as Markdown pages are compiled first and may be overwritten.

#### `_site` 
This is where the compiled static site is stored. You should not edit files here as they may get overwritten.

When publishing your site this is where you should serve the site from.


### Resource Directories
#### `config` 
The config directory contains configuration files. The most interesting one is probably `config/hyde.php` where you can set the site name!


#### `resources/assets`
This directory contains the frontend source files.

The default frontend resource files are as follows. Please see the chapter in the [Getting Started](getting-started.html) page to learn more.