---
navigation:
    label: "Creating Blog Posts"
    priority: 11
abstract: "Everything you need to start writing blog posts in HydePHP, from dropping a Markdown file in _posts to scaffolding one with front matter using the HydeCLI."
---

# Creating Blog Posts

## Introduction to Hyde Posts

Making blog posts with Hyde is easy. At the most basic level, all you need is to add a Markdown file to your `_posts` folder.

To use the full power of the Hyde post module you'll want to add YAML [Front Matter](front-matter) to your posts.

**You can interactively scaffold posts with automatic front matter using the HydeCLI:**

```terminal
php hyde make:post
```
Learn more about scaffolding posts, and other files, in the [console commands](console-commands) documentation.

## Short Video Tutorial

<iframe width="560" height="315" src="https://www.youtube-nocookie.com/embed/gjpE1U527h8" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>

## Best Practices and Hyde Expectations

Since Hyde does a lot of things automatically, there are some things you may need
to keep in mind when creating blog posts so that you don't get unexpected results.

### Filenames

- Markdown post files are stored in the `_posts` directory
- The filename is used as the filename for the compiled HTML
- Filenames should use `kebab-case-name` followed by the extension `.md`
- Files prefixed with `_underscores` are ignored by Hyde
- Your post will be stored in `_site/posts/<identifier>.html`

**Example:**

```terminal
✔ _posts/hello-world.md # Valid and will be compiled to _site/posts/hello-world.html
```

#### Date Prefixes

You **optionally** can set a blog post's publication date by prefixing the filename with a date in ISO 8601 format (`YYYY-MM-DD`). Optionally, you can also include the time (`HH-MM`).

```terminal
# Basic date prefix (sets date to 2024-11-05 00:00)
2024-11-05-my-first-post.md

# Date and time prefix (sets date to 2024-11-05 10:30)
2024-11-05-10-30-my-first-post.md
```

**The date prefix will be:**
1. Stripped from the route key (resulting in clean URLs like `posts/my-first-post`)
2. Used to set the post's publication date (unless explicitly defined in front matter)

**Important notes:**
- Dates must be in ISO 8601 format (`YYYY-MM-DD` or `YYYY-MM-DD-HH-MM`)
- Days and months must use leading zeros (e.g., `2024-01-05` not `2024-1-5`)
- Time is optional and uses 24-hour format with a hyphen separator (`HH-MM`)
- Front matter dates take precedence over filename dates
- Dates in the future mark the post as a [scheduled post](#scheduled-posts)
- Using date prefixes is entirely optional!

This feature provides an intuitive way to organize your blog posts chronologically while maintaining clean URLs, and matches the behavior of many popular static site generators for interoperability.

### Front Matter

Front matter is optional, but highly recommended for blog posts as the front matter is used to construct dynamic HTML
markup for the post as well as meta tags and post feeds.

You are encouraged to look at the compiled HTML to learn
and understand how your front matter is used. You can read more about the Front Matter format in the [Front Matter](front-matter) documentation.

### Blog Post Example

Before digging deeper into all the supported options, let's take a look at what a basic post with front matter looks like.

```markdown title="_posts/my-new-post.md"
---
title: My New Post
description: A short description used in previews and SEO
category: blog
author: Mr. Hyde
date: 2022-05-09 18:38
---

## Write Your Markdown Here

Lorem ipsum dolor sit amet, consectetur adipisicing elit.
Autem aliquid alias explicabo consequatur similique,
animi distinctio earum ducimus minus, magnam.
```

## Drafts and Scheduled Posts

Hyde supports two zero-configuration publication states for keeping a post out of your published site. Drafts are marked
with a front matter property, while scheduled posts are set by a date, which can come from either front matter or a
filename [date prefix](#date-prefixes). Neither state needs any configuration, command line flags, or a separate directory.

| State         | Meaning                      | `php hyde serve` | `php hyde build`      |
|---------------|------------------------------|------------------|-----------------------|
| Normal post   | Publish now                  | Included         | Included              |
| `draft: true` | Not approved for publication | Included         | Excluded indefinitely |
| Future date   | Finished, publish later      | Included         | Excluded until date   |

When you build the site, Hyde skips drafts and scheduled posts during auto-discovery, so they get no route, are not
compiled to `_site`, and do not appear in post listings, the sitemap, or the RSS feed.

### Drafts

Set `draft: true` to keep a post out of your built site while the property remains true:

```markdown title="_posts/work-in-progress.md"
---
title: Work in progress
draft: true
---
```

This suits a post that is unfinished, awaiting review, or that you want to temporarily take down without deleting or
moving the file. Unlike a future date, a draft never becomes publishable on its own: it stays out of your builds until
you remove `draft: true` or set it to `false`, at which point the normal date rules apply.

To publish the post, remove `draft: true`. You may set it to `false` instead, but because posts are published by
default, omitting the property keeps the front matter cleaner.

### Scheduled Posts

A post whose date is set in the future is scheduled: it is excluded from builds until that date has passed, and is then
published by the next build. This works with both front matter dates and [date prefixes](#date-prefixes):

```markdown title="_posts/my-upcoming-post.md"
---
title: My Upcoming Post
date: 2099-01-01
---
```

A post that is both a draft and dated in the future stays excluded even after its date passes, since the explicit draft
status is stronger than the date.

### Previewing Drafts and Scheduled Posts

Drafts and scheduled posts are only excluded when building the site. The development server treats your site as an
authoring preview, so both are included there and you can write and proofread them as normal:

| Command          | Drafts and scheduled posts                          |
|------------------|-----------------------------------------------------|
| `php hyde serve` | **Included** — the site as you are working on it    |
| `php hyde build` | **Excluded** — the site as your readers will see it |

There is nothing to configure and no front matter to temporarily change: just visit the post's normal URL while serving.

Note that this applies to everything the server renders, so a draft or scheduled post also appears in post listings and
feeds while serving. That is deliberate, as it lets you check how the post's card, category, image, excerpt, and ordering
behave before it goes live, not just the article itself.

### Scheduled Posts Do Not Publish Themselves

**Hyde is a static site generator, so nothing happens on your site until you build it again.**

A scheduled post is not published when its date passes. It is published by the first site build that runs after that
point. If you deploy your site once and leave it, a post dated next Tuesday will simply never appear.

To have a post go live on its own, run builds on a schedule. For example, with GitHub Actions:

```yaml title=".github/workflows/deploy.yml"
on:
  push:
    branches: [main]
  schedule:
    # Build every day at 06:00 UTC so scheduled posts get published
    - cron: '0 6 * * *'
```

Keep in mind that the post goes live at the first build after its date, not at the exact time you set. With the daily
schedule above, a post dated 09:00 is published by the next morning's run, not at 09:00 on the dot. Build more often if
you need tighter timing.

Also note that the date is compared against the time zone of the machine running the build,
which for CI runners is usually UTC rather than your local time zone.

Since a future date is what keeps a scheduled post out of your build, a mistyped date does the same thing. If a post is missing from
your built site, check that its date is not accidentally set ahead of the build, for example through a mistyped year,
and that it does not have a leftover `draft: true`. The post will still be visible while serving, which is a useful way
to confirm this is what happened.

## Supported Front Matter Properties

### Post Front Matter Schema

Here is a quick reference of the supported front matter properties.
Keep on reading to see further explanations, details, and examples.

| **KEY NAME**   | **VALUE TYPE** | **EXAMPLE / FORMAT**             |
|----------------|----------------|----------------------------------|
| `title`        | string         | "My New Post"                    |
| `description`  | string         | "A short description"            |
| `category`     | string         | "my favorite recipes"            |
| `date`         | string         | "YYYY-MM-DD [HH:MM]"             |
| `draft`        | bool           | true                             |
| `author`       | string/array   | _See [author](#author) section_  |
| `image`        | string/array   | _See [image](#image) section_    |

Note that YAML here is pretty forgiving. In most cases you do not need to wrap strings in quotes.
However, it can help in certain edge cases, for example if the text contains special Yaml characters, thus they are included here.

In the examples below, when there are multiple examples, they signify various ways to use the same property.

When specifying an array you don't need all the sub-properties. The examples generally show all the supported values.

### Title

```yaml
title: "My New Post"
```

### Description

```yaml
description: "A short description used in previews and SEO"
```

### Category

```yaml
category: blog
```

```yaml
category: "My favorite recipes"
```

### Date

```yaml
date: "2022-01-01"
```

```yaml
date: "2022-01-01 12:00"
```

Setting the date in the future marks the post as a [scheduled post](#scheduled-posts),
which is excluded from site builds until its publication date has passed.

### Draft

```yaml
draft: true
```

Marks the post as a [draft](#drafts), excluding it from site builds while it is true.
You may also set it to `false`, but as posts are published by default, that has no functional effect.

### Author

Specify a page author, either by a username for an author defined in the [`authors` config](customization#authors), or by an arbitrary name,
or by an array of author data.

#### Arbitrary name displayed "as is"

```yaml
author: "Mr. Hyde"
```

#### Username defined in `authors` config

```yaml
author: mr_hyde
```

#### Array of author data

```yaml
author:
    # These are used in the default author templates
    name: "Mr. Hyde"
    username: mr_hyde
    website: https://twitter.com/HydeFramework

    # These are not used in the default author templates, but can be used in your custom views
    bio: "The mysterious author of HydePHP"
    avatar: avatar.png
    socials:
      twitter: "@HydeFramework"
      github: "hydephp"
```

When using an array, you don't need to include all properties. Specified values will override the corresponding entries in the `authors` config.

Note: Author usernames are automatically normalized (converted to lowercase with spaces replaced by underscores).

### Image

Specify a cover image for the post, either by a local image path for a file in the `_media/` directory, or by a full URL.
Any array data is constructed into a dynamic fluent caption, and injected into post and page metadata.

#### Local image path

When supplying an image source with a local image path, the image is expected to be stored in the `_media/` directory.
Like all other media files, it will be copied to `_site/media/` when the site is built, so Hyde will resolve links accordingly.

```yaml
image: image.jpg
```

#### Full URL

Full URL starting with `http(s)://` or `//` (protocol-relative).
The image source will be used as-is, and no additional processing is done.

```yaml
image: https://cdn.example.com/image.jpg
```

#### Data-rich image and captions

You can also supply an array of data to construct a rich image with a fluent caption.

```yaml
image:
    source: Local image path or full URL
    altText: "Alt text for image"
    titleText: "Tooltip title"
    copyright: "Copyright (c) 2022"
    licenseName: "CC-BY-SA-4.0"
    licenseUrl: https://example.com/license/
    authorUrl: https://photographer.example.com/
    authorName: "John Doe"
    caption: "Overrides the fluent caption feature"
```

The data will then be used for metadata and to render a fluently worded caption. If you just want to add a quick caption, you can instead simply set the "caption field" to override the caption; or if you simply want a caption and no metadata this is a quick option as well.
```yaml
image:
    source: how-to-turn-your-github-readme-into-a-static-website-cropped.png
    alt: Example of a static website created from a GitHub Readme
    caption: Static website from GitHub Readme with **Markdown** support!
```

The caption field supports inline Markdown formatting like **bold**, *italic*, and [links](https://example.com). This makes it easy to add rich text formatting to your image captions.

If the `alt` field is missing, the caption will be used as the alt text as well.

## Using Images in Posts

To use images stored in the `_media/` directory, you can use the following syntax:

```markdown
![Image Alt](../media/image.png "Image Title")
```

_Note the relative path since the blog post is compiled to `posts/example.html`_

To learn more, check out the [managing assets chapter](managing-assets#managing-images) on the topic.
