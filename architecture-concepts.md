---
priority: 5
category: "Getting Started"
---

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
