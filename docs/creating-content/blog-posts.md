---
navigation:
    label: "Creating Blog Posts"
    priority: 11
---

# Creating Blog Posts

## Introduction to Hyde Posts

Making blog posts with Hyde is easy. At the most basic level,
all you need is to add a Markdown file to your `_posts` folder.

To use the full power of the Hyde post module however,
you'll want to add YAML Front Matter to your posts.

You can scaffold posts with automatic front matter using the HydeCLI:

```bash
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
- Filenames should use `kebab-case-slug` followed by the extension `.md`
- Files prefixed with `_underscores` are ignored by Hyde
- Your post will be stored in `_site/posts/<identifier>.html`

**Example:**

```bash
✔ _posts/hello-world.md # Valid and will be compiled to _site/posts/hello-world.html
```

### Front Matter

Front matter is optional, but highly recommended for blog posts as the front matter is used to construct dynamic HTML
markup for the post as well as meta tags and post feeds.

You are encouraged to look at the compiled HTML to learn
and understand how your front matter is used. You can read more about the Front Matter format in the [Front Matter](front-matter) documentation.

### Blog Post Example

Before digging in deeper on all the supported options, let's take a look at what a basic post with front matter looks like.

```markdown
// filepath _posts/my-new-post.md
---
title: My New Post
description: A short description used in previews and SEO
category: blog
author: Mr. Hyde
date: 2022-05-09 18:38
---

## Write your Markdown here

Lorem ipsum dolor sit amet, consectetur adipisicing elit.
Autem aliquid alias explicabo consequatur similique,
animi distinctio earum ducimus minus, magnam.
```


## Supported Front Matter properties

### Post Front Matter Schema

Here is a quick reference of the supported front matter properties.
Keep on reading to see further explanations, details, and examples.

| **KEY NAME**   | **VALUE TYPE** | **EXAMPLE / FORMAT**             |
|----------------|----------------|----------------------------------|
| `title`        | string         | "My New Post"                    |
| `description`  | string         | "A short description"            |
| `category`     | string         | "my favorite recipes"            |
| `date`         | string         | "YYYY-MM-DD [HH:MM]"             |
| `author`       | string/array   | _See [author](#author) section_  |
| `image`        | string/array   | _See [image](#image) section_    |

Note that YAML here is pretty forgiving. In most cases you do not need to wrap strings
in quotes, but it can help in certain edge cases, thus they are included here.

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

### Author

Specify a page author, either by a username for an author defined in the `authors` config, or by an arbitrary name,
or by an array of author data. See the [Post Author](#post-authors) section for more details.

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
    name: "Mr. Hyde"
    username: mr_hyde
    website: https://twitter.com/HydeFramework
```

When specifying an array you don't need all the sub-properties. The example just shows all the supported values.
Array values here will override all the values in the `authors` config entry.

### Image

Specify a cover image for the post, either by a local image path for a file in the `_media/` directory, or by a full URL.

#### Local image path

When supplying an image source with a local image path, the image is expected to be stored in the `_media/` directory.
Like all other media files, it will be copied to `_site/media/` when the site is built, so Hyde will resolve links accordingly.

```yaml
image: image.jpg
```

```yaml
image: image.jpg # Expanded by Hyde to `_media/image.jpg` and is resolved automatically to the correct URL for the built site
image: https://cdn.example.com/image.jpg # Full URL starting with `http(s)://`) or `//` (protocol-relative)
image:
    source: image.jpg # Same as above
    source: https://cdn.example.com/image.jpg # Same as above
    altText: "Alt text for image"
    title: "Tooltip title"
    copyright: "Copyright (c) 2022"
    licenseName: "CC-BY-SA-4.0"
    licenseUrl: https://example.com/license/
    authorUrl: https://photographer.example.com/
    authorName: "John Doe"
```

When supplying an image source with a local image path, the image is expected to be stored in the `_media/` directory.
Like all other media files, it will be copied to `_site/media/` when the site is built, so Hyde will resolve links accordingly.

When supplying an image with a full URL, the image source will be used as-is, and no additional processing is done.

The image will be used as the cover image, and any array data is constructed into a dynamic fluent caption,
and injected into post and page metadata.

> See [posts/introducing-images](https://hydephp.com/posts/introducing-images)
> for a detailed blog post with examples and schema information!
{ .info }


## Using images in posts

To use images stored in the `_media/` directory, you can use the following syntax:

```markdown
![Image Alt](../media/image.png "Image Title") # Note the relative path since the blog post is compiled to `posts/example.html`
```

To learn more, check out the [chapter in managing assets](managing-assets#managing-images)
