---
navigation:
    priority: 10
---

# Front Matter

>info **In a nutshell:** Front Matter is a block of YAML containing metadata, stored at the top of a Markdown file.

Front matter is heavily used in HydePHP to store metadata about pages. Hyde uses the front matter data to generate rich and dynamic content.
For example, in a blog post you may define a category, author website, or featured image. In a documentation page you may define the sidebar priority or label.

Using front matter is optional, as Hyde will dynamically generate data based on the content itself. (Though any matter you provide will take precedence over the automatically generated data.)
While Hyde offers some support for front matter within Blade files, most of the time you use front matter, it will be in Markdown.


## Front matter syntax

Here's a refresher on Yaml, and a quick reference of the syntax Hyde uses and expects:

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

Strings don't need to be quoted, but it can help in certain edge cases, thus they are included here.

### Front Matter in Markdown

All Markdown content files support Front Matter. Blog posts for example make heavy use of it.

The specific usage and schemas used for pages are documented in their respective documentation, however, here is a primer on the fundamentals.

- Front matter is stored in a block of YAML that starts and ends with a `---` line.
- The front matter should be the very first thing in the Markdown file.
- Each key-pair value should be on its own line.

**To use Front Matter, add Yaml to the top of your Markdown file:**

```markdown
---
title: "My New Post"
author:
  name: "John Doe"
  website: https://example.com
---

## Markdown comes here

Lorem ipsum dolor sit amet, etc.
```

### Front Matter in Blade

HydePHP has experimental support for creating front-matter in Blade templates, called BladeMatter.

The actual syntax does not use YAML; but instead PHP. However, the parsed end result is the same. Please note that
BladeMatter currently does not support multidimensional arrays or multi-line directives as the data is statically parsed.

To create BladeMatter, you simply use the default Laravel Blade `@php` directive to declare a variable in the template.

```blade
@php($title = 'BladeMatter Demo')
```

It will then be available through the global `$page` variable, `$page->matter('title')`.
