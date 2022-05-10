---
label: "Using Markdown"
---

# Markdown with Hyde

Hyde makes heavy use of Markdown. While this page won't teach you how to use Markdown,
it will hopefully shed some light about how Hyde uses it, and how you can extend it using Front Matter.

## Front Matter

All Markdown content files support Front Matter. Blog posts for example make heavy use of it.

The specific usage and schemas used for pages are documented in their respective documentation,
however, here is a primer on the fundamentals.

- Front matter is stored in a block of YAML that starts and ends with a `---` line.
- The front matter should be the very first thing in the Markdown file.
- Each key-pair value should be on its own line.

**Example:**
```markdown
---
title: "My New Post"
author:
  name: "John Doe"
  website: https://mrhyde.example.com
---

## Markdown comes here

Lorem ipsum dolor sit amet, etc.
```

## CommonMark environment

Hyde uses [League CommonMark](https://commonmark.thephpleague.com/) for converting Markdown into HTML.

### Customizing the environment 

Hyde ships with the Github Flavored Markdown extension, and 
the Torchlight extension is enabled automatically when needed.

You can add extra CommonMark extensions, or change the default ones, in the `config/markdown.php` file.

```php
'extensions' => [
	\League\CommonMark\Extension\GithubFlavoredMarkdownExtension::class,
	\League\CommonMark\Extension\Attributes\AttributesExtension::class,
	\League\CommonMark\Extension\DisallowedRawHtml\DisallowedRawHtmlExtension::class,
],
```

In the same file you can also change the config to be passed to the CommonMark environment.

```php
'config' => [
	'disallowed_raw_html' => [
		'disallowed_tags' => [],
	],
],
```
