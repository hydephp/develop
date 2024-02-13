---
navigation:
    label: "Advanced Markdown"
    priority: 27
---

# Advanced Markdown

## Introduction

Since HydePHP makes heavy use of Markdown, there are some extra features and helpers created just for Hyde to make using Markdown even easier and more powerful!


## Using Blade in Markdown

A special feature in Hyde, is that you can use [Laravel Blade](https://laravel.com/docs/10.x/blade) in Markdown files!

To use Blade in your Markdown files, simply use the Blade shortcode directive, followed by your desired Blade string.

### Standard syntax

```markdown
 [Blade]: {{ "Hello World!" }} // Will render: 'Hello World!'
```

### Blade includes

Only single-line shortcode directives are supported. If you need to use multi-line Blade code, use an `@include`
directive to render a more complex Blade template. You can pass data to includes by specifying an array to the second argument.

```markdown
 [Blade]: @include("hello-world")
 [Blade]: @include("hello", ["name" => "World"])
```

### Enabling Blade-supported Markdown

The feature is disabled by default since it allows arbitrary PHP to run, which could be a security risk, depending on your setup.
However, if your Markdown is trusted, and you know it's safe, you can enable it in the `config/markdown.php` file.

```php
// filepath: config/markdown.php
'enable_blade' => true,
```

### Limitations

All shortcodes must be the first word on a new line, and only single-line shortcodes are supported.


## Coloured Blockquotes

The HydePHP Markdown converter also supports some extra directives and features. One of them being four different
coloured blockquotes. Simply append the desired colour after the initial `>` character.

```markdown
‎> Normal Blockquote
‎>info Info Blockquote
‎>warning Warning Blockquote
‎>danger Danger Blockquote
‎>success Success Blockquote
```

> Normal Blockquote
>info Info Blockquote
>warning Warning Blockquote
>danger Danger Blockquote
>success Success Blockquote

### Customizations

You can easily customize these styles too by adding and editing the following in your `resources/app.css` file, and then recompiling your site styles.
The code examples here use the Tailwind `@apply` directives, but you could also use `border-color: something;` just as well.

```css
/* filepath resources/app.css

/* Markdown Features */

.prose blockquote.info {
    @apply border-blue-500;
}

.prose blockquote.success {
    @apply border-green-500;
}

.prose blockquote.warning {
    @apply border-amber-500;
}

.prose blockquote.danger {
    @apply border-red-600;
}
```

### Markdown usage

The coloured blockquotes also support inline Markdown, just like normal blockquotes.

```markdown
‎>info Formatting is **supported**!
```

### Limitations

Note that these currently do not support multi-line blockquotes.


## Code Block Filepaths

When browsing these documentation pages you may have noticed a label in the top right corner of code blocks specifying the file path.
These are also created by using a custom Hyde feature that turns code comments into automatic code blocks.

### Usage

Simply add a code comment with the path in the **first line** of a fenced code block like so:

````markdown
// Filepath: _docs/advanced-markdown.md
```php
‎// Filepath: hello-world.php

echo 'Hello World!';
```
````

Which becomes:

```php
// Filepath: hello-world.php

echo 'Hello World!';
```

### Alternative syntax

The syntax is rather forgiving, by design, and supports using both `//` and `#` for comments.
The colon is also optional, and the 'filepath' string is case-insensitive. So the following is also perfectly valid:

````markdown
```js
‎// filepath hello.js
console.log('Hello World!');
```
````

If you have a newline after the filepath, like in the first example, it will be removed so your code stays readable.

### Advanced usage

If you have enabled HTML in Markdown by setting the `allow_html` option to true in your `config/markdown.php` file,
anything within the path label will be rendered as HTML. This means you can add links, or even images to the label.

````markdown
// Filepath: <a href="https://github.com/hydephp/develop/blob/master/docs/digging-deeper/advanced-markdown.md" rel="nofollow noopener" target="_blank">View file on Github</a>
```markdown
‎// Filepath: <a href="https://github.com">View file on Github</a>
```
````

### Limitations

The filepaths are hidden on mobile devices using CSS to prevent them from overlapping with the code block.


## Configuration

### Full configuration reference

All Markdown-related configuration options are in the `config/markdown.php` file.
You can find the full reference on the [Customization](customization#markdown-configuration) page.

### Raw HTML Tags

To convert Markdown, HydePHP uses the GitHub Flavored Markdown extension, which strips out potentially unsafe HTML.
If you want to allow all arbitrary HTML tags, and understand the risks involved, you can enable all HTML tags by setting
the `allow_html` option to `true` in your `config/markdown.php` file.

```php
// filepath: config/markdown.php
'allow_html' => true,
```

This will add and configure the `DisallowedRawHtml` CommonMark extension so that no HTML tags are stripped out.

### Tailwind Typography Prose Classes

HydePHP uses the [Tailwind Typography](https://tailwindcss.com/docs/typography-plugin) to style rendered Markdown.
We do this by adding the `.prose` CSS class to the HTML elements containing the rendered Markdown, using the built-in Blade components.

You can easily edit these classes, for example if you want to customize the prose colours, in the `config/markdown.php` file.

```php
// filepath: config/markdown.php
'prose_classes' => 'prose dark:prose-invert', // [tl! remove]
'prose_classes' => 'prose dark:prose-invert prose-img:inline', // [tl! add]
```

Please note that if you add any new classes, you may need to recompile your CSS file.
