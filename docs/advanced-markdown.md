---
label: "Advanced Markdown"
priority: 26
category: "Digging Deeper"
---

# Advanced Markdown

## Introduction

Since HydePHP makes heavy use of Markdown there are some extra features and helpers created just for Hyde to make using Markdown even easier!

## Raw HTML Tags

HydePHP uses the GitHub Flavored Markdown extension to convert Markdown. This, by default strips out some HTML tags. If you want to allow all arbitrary HTML tags, and understand the risks involved, enable all HTML tags by setting the following option to true in your `config/markdown.php` file.

```php
// filepath: config/markdown.php
// torchlight! {"lineNumbers": false}
'allow_html' => true,
```

This, will behind the scenes add the bundled `DisallowedRawHtml` extension, and configure it so that no HTML tags are stripped out.

## Blade Support

Since Hyde v0.30.x you can use Laravel Blade in Markdown files!

### Using Blade in Markdown

To use Blade in your Markdown files, simply use the Blade shortcode directive,
followed by your desired Blade string.

#### Standard syntax

```markdown
 [Blade]: {{ "Hello World!" }} // Will render: 'Hello World!'
```

#### Blade includes

Only single-line shortcode directives are supported. If you need to use multi-line Blade code,
use an `@include` directive to render a more complex Blade template. 
You can pass data to includes by specifying an array to the second argument.

```markdown
 [Blade]: @include("hello-world")
 [Blade]: @include("hello", ["name" => "World"])
```

### Enabling Blade-supported Markdown
It's disabled by default since it allows arbitrary PHP to run, which could be a security risk,
depending on your setup. However, if your Markdown is trusted, and you know it's safe,
you can enable it in the `config/markdown.php` file.

```php
// torchlight! {"lineNumbers": false}
'enable_blade' => true,
```

#### Limitations

All shortcodes must be the first word on a new line.
For example, using a space before the `[Blade]:` will intentionally cause it to not render.
