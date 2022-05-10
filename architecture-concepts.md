---
priority: 5
category: "Getting Started"
---

# Some key concepts in Hyde

## The HydeCLI

When you are not writing Markdown and Blade, most of your interactions with Hyde will be through the
Hyde Command Line Interface (CLI). 
Since the CLI is based on the Laravel Artisan Console, so you may actually already be familiar with it.

You should take a look at [the Console Commands page](console-commands.html)
to learn more and see the available commands and their usage.

```bash
php hyde <command> [--help]
```

## File Autodiscovery

Content files, meaning source Markdown and Blade files, are automatically
discovered by Hyde and compiled to HTML when building the site.
This means that you don't need to worry about routing and controllers!

The directory a source file is in will determine the Blade template that is used to render it.

Here is an overview of the content source directories, the output directory of the compiled files,
and the file extensions supported by each. Files starting with an `_underscore` are ignored.

| Page/File Type | Source Directory | Output Directory | File Extensions     |
|----------------|------------------|------------------|---------------------|
| Static Pages   | `_pages/`        | `_site/`         | `.md`, `.blade.php` |
| Blog Posts     | `_posts/`        | `_site/posts/`   | `.md`               |
| Documentation  | `_docs/`         | `_site/docs/`    | `.md`               |
| Media Assets   | `_media/`        | `_site/media/`   | See full list below |

<small>
<blockquote>
Media file types supported: `.png`, `.svg`, `.jpg`, `.jpeg`, `.gif`, `.ico`, `.css`, `.js`
</blockquote>
</small>

## Convention over Configuration

Hyde favours the "Convention over Configuration" paradigm and thus comes preconfigured with sensible defaults.
However, Hyde also strives to be modular and endlessly customizable hackable if you need it. 
Take a look at the [customization and configuration guide](customization.html) to see the endless options available!

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
