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

## Write something awesome.

Lorem ipsum dolor sit amet, consectetur adipisicing elit.
Autem aliquid alias explicabo consequatur similique,
animi distinctio earum ducimus minus, magnam.
```

## How the Front Matter is used

It may be helpful to take a look at how the front matter data is
used so you can get a better understanding of how it works.

Here is the compiled HTML created by Hyde with dynamically generated markup 
data based on the front matter, config settings, and Hyde accessibility defaults.
This can look quite overwhelming, so we'll dig in deeper later on in the article.

```html
<article aria-label="Article" id="https://hydephp.github.io/posts/my-new-post" itemtype="https://schema.org/Article">
	<meta itemprop="identifier" content="my-new-post">
	<meta itemprop="url" content="https://hydephp.github.io/posts/my-new-post">
	<header aria-label="Header section" role="doc-pageheader">
		<h1 itemprop="headline">My New Post</h1>
		<div id="byline" aria-label="About the post" role="doc-introduction">
			Posted <time itemprop="dateCreated datePublished" datetime="2022-05-09T18:38:00+00:00" title="Monday May 9th, 2022, at 6:38pm">May 9th, 2022</time>
			by author
			<address itemprop="author" itemtype="https://schema.org/Person" aria-label="The post author"> 
				<span itemprop="name" aria-label="The author's name" title="@mr_hyde">Mr. Hyde</span> 
			</address>
			in the category "blog"
		</div>
	</header>
	<div aria-label="Article body" itemprop="articleBody">
		<h2>Write something awesome.</h2>
		<p>Lorem ipsum dolor sit amet.</p>
	</div>
	<span class="sr-only">End of article</span>
</article>
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

#### Used in
- Article `<H1>` element
- HTML `<title>` tag
- Meta `og:title` property
- Article `headline` property
- Blog post excerpt title


### Post Description

#### Example
```yaml
description: "A short description used in previews and SEO" # (string)
```

#### Used in
- Meta `description` property
- Blog post excerpts

### Post Category

#### Example

```yaml
category: blog # (string)
```

#### Used in
- Meta `keywords` property
- Article category in the byline 

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

#### Used in
- Article `<address>` element
- Meta `author` property
- Article `author` property, which can contain the following if present in config or front matter array:
  - Itemtype `https://schema.org/Person`
  - `URL` property and `rel link` to author's website


### Post Date

#### Example

```yaml
date: "2022-01-01 12:00" # (string: YYYY-MM-DD [HH:MM])
```

#### Used in
- Article `<time>` element with `datetime` attribute and `dateCreated` `datePublished` properties
- Meta `og:article:published_time` property

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

#### Used in
- Article `doc-cover` image
- If supplied with extra data, the following can be added to the `ImageObject` property:
  - Alt text for screenreaders using the `image.description` value
  - Tooltip using `title=""` attribute
  - `Person` object for the image author with `creator` property
  - `copyrightNotice` property
  - `license` property
    - If supplied with a URL, a `rel="license` link is added
  - `image` (url) property
  - Meta `text` property
  - Meta `name` property
  - Meta `og:image` property
- All of the array data is constructed into a dynamic fluent string used in the `<figcaption>` element