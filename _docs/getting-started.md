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

After creating your post run `php hyde build` to build your site! You should also look at the section dedicated to building the site if you have not already.

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
// This section will get extracted into a blog post





Once


#### Deep-dive
> Deepdives take a closer look into how a feature works behind the scenes. While not required to know it can help to understand the "magic" behind Hyde.



### Hyde Docs 


### Hyde Pages


