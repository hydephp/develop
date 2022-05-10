---
---

# Creating Blog Posts

## Introduction to Hyde Posts

Making blog posts with Hyde is easy. At the most basic level,
all you need is to add a Markdown file to your _posts folder.

To use the full power of the Hyde post module however,
you'll want to add YAML Front Matter to your posts.

You can scaffold posts with automatic front matter using the HydeCLI:
```bash
php hyde make:post
```
Learn more about scaffolding posts, and other files, in the [console commands](console-commands.html) documentation.


## Short Video Tutorial

<iframe width="560" height="315" src="https://www.youtube-nocookie.com/embed/gjpE1U527h8" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>

## Best Practices and Hyde Expectations

Since Hyde does a lot of things automatically, there are some things you may need
to keep in mind when creating blog posts so that you don't get unexpected results.

### Filenames

- Markdown post files are stored in the `_posts` directory.
- The filename is used as the filename for the compiled HTML
- Filenames should use `kebab-case-slug` followed by the extension `.md`
- Files prefixed with `_underscores` are ignored by Hyde

**Example:**
```bash
✔ _posts/hello-world.md # Valid and will be compiled to _site/posts/hello-world.html
```

### Front Matter

Front matter is optional, but highly recommended for blog posts.

- Front matter is stores in a block of YAML that starts and ends with a `---` line.
- The front matter should be the very first thing in the Markdown file.
- Each key-pair value should be on its own line.
- The front matter is used to construct dynamic HTML markup for the post as well as meta tags and post feeds.
  You are encouranged to look at the compiled HTML to learn and understand how your front matter is used.


**Example:**
```markdown
---
title: "My New Post"
---

## Markdown comes here
```

You can use the `php hyde make:post` command to automatically generate the front matter based on your input.



## A first look at Front Matter

Before digging in deeper on all the supported front matter options,
let's take a look at what a basic post with front matter looks like.

This file was created using the `make:post` by hitting the `Enter` key to use
all the defaults (with some extra lorem ipsum to illustrate).

```markdown {: filepath="_posts/my-new-post.md"}
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

### How the Front Matter is used

The front matter is used to construct dynamic HTML markup for the post as well as meta tags and post feeds.

You are encouranged to look at the compiled HTML to learn and understand how your front matter is used.

### Front matter syntax

Here is a quick reference of the syntax Hyde uses and expects:

```markdown
---
key: value
string: "quoted string"
boolean: true
integer: 100
array:
  key: value
  key: value
---
```

## Supported Front Matter properties

Now that we have a foundation of how Hyde uses front matter to create data-rich HTML markup,
let's take a look at the supported front matter properties and some usage examples.
Feel free to mix and match between them in your posts.

### Quick Reference
Here is a quick reference with some examples. Duplicate properties showcase different ways to use the same property.

```yaml
title: "My New Post" # (string)
description: "A short description" # (string)
category: blog # (string)
author: "Mr. Hyde" # (string: arbitrary name)
author: "mr_hyde" # (string: username defined in config/authors.yml)
author: # (array)
  name: Mr. Hyde # (string)
  username: mr_hyde # (string)
  website: https://mrhyde.example.com # (string: URL/link to author)
date: "2022-05-09 18:38" # (string) 
date: "2022-05-09" # (string) 
image: image.jpg # (string: filename of image in `_media` directory)
image: https://example.com/media/image.jpg # (string: full URL of image)
image: # (array)
  path: image.jpg # (string: filename of image in `_media` directory)
  uri: https://example.com/media/image.jpg # (string: full URL of image)
  title: Tooltip # (string)
  description: Alt text # (string)
  copyright: Copyright (c) 2022 # (string)
  license: MIT # (string)
  licenseUrl: https://example.org/licenses/MIT # (string: URL/link to license)
  credit: https://example.org/photographers/mr_hyde # (string: URL/link to image author)
  author: mr_hyde # (string: name/username of image author)
```

### Post Title

#### Example
```yaml
title: "My New Post" # (string)
```

### Post Description

#### Example
```yaml
description: "A short description used in previews and SEO" # (string)
```

### Post Category

#### Example

```yaml
category: blog # (string)
```

### Post Author

#### Examples

```yaml
author: "Mr. Hyde" # Arbitrary name (string)
```

```yaml
author: mr_hyde # Username for author defined in the config file `authors.yml` (string)
```

```yaml
author: # Array of author data (array)
  name: "Mr. Hyde" # (string)
  username: mr_hyde # (string)
  website: https://mrhyde.example.com # (string)
```

### Post Date

#### Example

```yaml
date: "2022-01-01 12:00" # (string: YYYY-MM-DD [HH:MM])
```

### Featured Image

#### Basic usage example

```yaml
image: image.jpg # (string of file in the `_media` directory)
```

```yaml
image: https://cdn.example.com/image.jpg # (string of full URL starting with `http(s)://`)
```

#### Advanced usage example

The image can also be set as an array which supports a whole set of options.

> See [posts/introducing-images](https://hydephp.github.io/posts/introducing-images.html) 
> for a detailed blog post with examples and schema information!

```yaml
image:
  description: "Image of a small kitten with its head tilted, sitting in a basket weaved from nature material."
  title: "Kitten Gray Kitty [sic]"
  uri: https://raw.githubusercontent.com/hydephp/hydephp.github.io/gh-pages/media/kitten-756956_640-min.jpg
  copyright: Copyright (c) 2022
  license: Pixabay License
  licenseUrl: https://pixabay.com/service/license/
  credit: https://pixabay.com/photos/kitten-gray-kitty-kitty-756956/
  author: Godsgirl_madi
```

The image is used as the post cover image, and all the array data is constructed
into a dynamic fluent caption, and injected into post and page metadata.