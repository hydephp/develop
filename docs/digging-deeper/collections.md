---
priority: 35
label: "File-based Collections"
---

# File-based Collections

>info This article covers advanced usage intended for those who are writing their own Blade views, and is not required as Hyde comes pre-packaged with many templates for you to use.

>warning This feature was added in v0.43.0-beta.

## Introduction to Hyde Data Collections

Hyde provides `DataCollections`, a subset of [Laravel Collections](https://laravel.com/docs/9.x/collections) giving you a similar developer experience to working with Eloquent Collections, but here, it's all entirely file-based.

You get the have access to all Laravel Collection methods so you are encouraged to read the [Laravel Collections documentation](https://laravel.com/docs/9.x/collections) for more information.

Currently only a Markdown collection type is added, but more types like YAML are planned.

### Enabling the feature

You may need to enable the module by adding the feature to your Hyde configuration file's `features` array:

```php
// filepath config/hyde.php

   'features' => [
        Features::dataCollections(),
    ],

```

### High-Level Concept Overview

To make collections easy to use and understand, Hyde makes a few assumptions about the structure of your collections. Follow these conventions and creating dynamic static sites will be a breeze.

1. Collections are stored in the new `_data` directory.
2. Each subdirectory in here can be a collection. 
3. Data collections are automatically generated when you use the Facade you will learn about below.
4. When using one of the facades, you need to specify the collection name, this name is the name of the subdirectory.
5. Each subdirectory should probably only have the same filetype to prevent developer confusion, but this is not enforced.
6. Unlike Markdown pages, files starting with underscores are not ignored.
7. You can customize the base `_data` directory through a service provider.


### Markdown Collections - Hands on Guide

#### Setting up the file structure

I think the best way to explain DataCollections is through examples. Let's create a Blade page with customer testimonials.

We start by setting up our directory structure. We will create a `testimonials` subdirectory, which will be the collection name.

In it we will place Markdown files. Each file will be a testimonial. The Markdown will be parsed into a MarkdownDocument object which parses any optional YAML front matter. 

Here is the sample Markdown we will use:

```blade
// filepath: _data/testimonials/1.md
---
author: John Doe
---

Lorem ipsum dolor sit amet, consectetur adipiscing elit...
```

Let's take a look at our directory structure. I just copied the same file a few times. You can name the files anything you want, I kept it simple and just numbered them.

```tree
_data
└── testimonials
    ├── 1.md
    ├── 2.md
    ├── 3.md
    ├── 4.md
    └── 5.md
```

#### Using the Facade to Access the Collections

Now for the fun part! We will use the `MarkdownCollection` facade to access all our files into a convenient object. The class is already aliased to the facade, so you don't need to use any namespaces.

The general syntax to use the facade is as follows:

```blade
MarkdownCollection::get('subdirectory_name')
```

This will return a Hyde DataCollection object, containing our Markdown files as MarkdownDocument objects. Here is a quick look at the object the facade returns:

<pre class="sf-dump" id="sf-dump-703277111" data-indent-pad="  " tabindex="0" aria-label="Sample output" style="white-space: pre-wrap; padding: 5px;  color: rgb(255, 132, 0); font-variant-numeric: normal; font-variant-east-asian: normal; font-stretch: normal; font-size: 12px; line-height: normal; font-family: Menlo, Monaco, Consolas, monospace; overflow-wrap: break-word; position: relative; z-index: 99999; word-break: break-all; overflow: initial !important;"><code><span class="sf-dump-default" style="display: inline; font-variant-numeric: normal; font-variant-east-asian: normal; font-stretch: normal; line-height: normal; overflow-wrap: break-word; position: relative; z-index: 99999; word-break: break-all;">^</span><span class="sf-dump-default" style="display: inline; font-variant-numeric: normal; font-variant-east-asian: normal; font-stretch: normal; line-height: normal; overflow-wrap: break-word; position: relative; z-index: 99999; word-break: break-all;"> </span><span class="sf-dump-note" style="display: inline; color: rgb(18, 153, 218);">Hyde\DataCollections\DataCollection</span> {<a class="sf-dump-ref sf-dump-toggle" title="[Ctrl+click] Expand all children" style="text-decoration: none;  border-style: initial; border-color: initial; outline: none; color: rgb(160, 160, 160);">#270 ▼</a><samp data-depth="1" class="sf-dump-expanded"> <br>&nbsp;&nbsp;+<span class="sf-dump-public" title="Public property" style="display: inline; color: rgb(255, 255, 255);">key</span>: "<span class="sf-dump-str" title="12 characters" style="display: inline; font-weight: bold; color: rgb(86, 219, 58);">testimonials</span>" <!-- <br>&nbsp;&nbsp;+<span class="sf-dump-public" title="Public property" style="display: inline; color: rgb(255, 255, 255);">parseTimeInMs</span>: <span class="sf-dump-num" style="display: inline; font-weight: bold; color: rgb(18, 153, 218);">5.02</span>  --><br>&nbsp;&nbsp;#<span class="sf-dump-protected" title="Protected property" style="display: inline; color: rgb(255, 255, 255);">items</span>: <span class="sf-dump-note" style="display: inline; color: rgb(18, 153, 218);">array:5</span> [<a class="sf-dump-ref sf-dump-toggle" title="[Ctrl+click] Expand all children" style="text-decoration: none;  border-style: initial; border-color: initial; outline: none; color: rgb(160, 160, 160);">▼</a><samp data-depth="2" class="sf-dump-expanded"> <br>&nbsp;&nbsp;&nbsp;&nbsp;<span class="sf-dump-index" style="display: inline; color: rgb(18, 153, 218);">0</span> =&gt; <span class="sf-dump-note" title="Hyde\Framework\Models\Markdown\MarkdownDocument <br>&nbsp;&nbsp;" style="display: inline; color: rgb(18, 153, 218); "><span class="sf-dump-ellipsis sf-dump-ellipsis-note" style="display: inline-block; text-overflow: ellipsis;  white-space: nowrap; overflow: hidden; vertical-align: top;">Hyde\Framework\Models</span><span class="sf-dump-ellipsis sf-dump-ellipsis-note" style="display: inline-block; text-overflow: ellipsis; max-width: none; white-space: nowrap; overflow: hidden; vertical-align: top;">\</span>MarkdownDocument</span> {<a class="sf-dump-ref sf-dump-toggle" title="[Ctrl+click] Expand all children" style="text-decoration: none;  border-style: initial; border-color: initial; outline: none; color: rgb(160, 160, 160);">#273 ▼</a><samp data-depth="3" class="sf-dump-expanded"> <br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;+<span class="sf-dump-public" title="Public property" style="display: inline; color: rgb(255, 255, 255);">matter</span>: <span class="sf-dump-note" style="display: inline; color: rgb(18, 153, 218);">array:1</span> [<a class="sf-dump-ref sf-dump-toggle" title="[Ctrl+click] Expand all children" style="text-decoration: none;  border-style: initial; border-color: initial; outline: none; color: rgb(160, 160, 160);">▶</a>] <br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;+<span class="sf-dump-public" title="Public property" style="display: inline; color: rgb(255, 255, 255);">body</span>: "<span class="sf-dump-str" title="59 characters" style="display: inline; font-weight: bold; color: rgb(86, 219, 58);">Lorem ipsum dolor sit amet, consectetur adipiscing elit...<span class="sf-dump-default sf-dump-ns" style="display: inline; color: rgb(255, 132, 0); font-variant-numeric: normal; font-variant-east-asian: normal; font-weight: normal; font-stretch: normal; line-height: normal; font-family: Menlo, Monaco, Consolas, monospace; overflow-wrap: break-word; position: relative; z-index: 99999; word-break: break-all; user-select: none;">\n</span></span>" <br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;+<span class="sf-dump-public" title="Public property" style="display: inline; color: rgb(255, 255, 255);">title</span>: "" <br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;+<span class="sf-dump-public" title="Public property" style="display: inline; color: rgb(255, 255, 255);">slug</span>: "" <br>&nbsp;&nbsp;&nbsp;&nbsp;</samp>} <br>&nbsp;&nbsp;&nbsp;&nbsp;<span class="sf-dump-index" style="display: inline; color: rgb(18, 153, 218);">1</span> =&gt; <span class="sf-dump-note" title="Hyde\Framework\Models\Markdown\MarkdownDocument <br>&nbsp;&nbsp;" style="display: inline; color: rgb(18, 153, 218); "><span class="sf-dump-ellipsis sf-dump-ellipsis-note" style="display: inline-block; text-overflow: ellipsis;  white-space: nowrap; overflow: hidden; vertical-align: top;">Hyde\Framework\Models</span><span class="sf-dump-ellipsis sf-dump-ellipsis-note" style="display: inline-block; text-overflow: ellipsis; max-width: none; white-space: nowrap; overflow: hidden; vertical-align: top;">\</span>MarkdownDocument</span> {<a class="sf-dump-ref sf-dump-toggle" title="[Ctrl+click] Expand all children" style="text-decoration: none;  border-style: initial; border-color: initial; outline: none; color: rgb(160, 160, 160);">#274 ▶</a>} <br>&nbsp;&nbsp;&nbsp;&nbsp;<span class="sf-dump-index" style="display: inline; color: rgb(18, 153, 218);">2</span> =&gt; <span class="sf-dump-note" title="Hyde\Framework\Models\Markdown\MarkdownDocument <br>&nbsp;&nbsp;" style="display: inline; color: rgb(18, 153, 218);"><span class="sf-dump-ellipsis sf-dump-ellipsis-note" style="display: inline-block; text-overflow: ellipsis;  white-space: nowrap; overflow: hidden; vertical-align: top;">Hyde\Framework\Models</span><span class="sf-dump-ellipsis sf-dump-ellipsis-note" style="display: inline-block; text-overflow: ellipsis; max-width: none; white-space: nowrap; overflow: hidden; vertical-align: top;">\</span>MarkdownDocument</span> {<a class="sf-dump-ref sf-dump-toggle" title="[Ctrl+click] Expand all children" style="text-decoration: none;  border-style: initial; border-color: initial; outline: none; color: rgb(160, 160, 160);">#275 ▶</a>} <br>&nbsp;&nbsp;&nbsp;&nbsp;<span class="sf-dump-index" style="display: inline; color: gray;">[The rest is truncated to conserve space...]</span> <!-- <br>&nbsp;&nbsp;</samp>] <br>&nbsp;&nbsp;#<span class="sf-dump-protected" title="Protected property" style="display: inline; color: rgb(255, 255, 255);">escapeWhenCastingToString</span>: <span class="sf-dump-const" style="display: inline; font-weight: bold;">false</span> </samp> <br>}--></code></pre>


#### Implementing it in a Blade view

Let's create a Blade page to display all our testimonials.

```bash
php hyde make:page "Testimonials" --type="blade"
# or just touch _pages/testimonials.blade.php
```

And we can use the collection almost like any other Laravel one. As you can see, we are able to get the author from the front matter, and the content from the body.

```blade
@foreach(MarkdownCollection::get('testimonials') as $testimonial)
<blockquote>
	<p>{{ $testimonial->body }}</p>
	<small>{{ $testimonial->matter['author'] }}</small>
</blockquote>
@endforeach
```

