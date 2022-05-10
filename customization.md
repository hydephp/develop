---
label: "Customizing your Site"
priority: 25
---

## CommonMark environment

Hyde uses [League CommonMark](https://commonmark.thephpleague.com/) for converting Markdown into HTML.

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
