# AI Prompting Helpers

## Context string

### Code generation

Add this to the bottom of your prompts:

```markdown
For context: This is for static site generator HydePHP, based on Laravel.

Motto: "Simplicity first. Power when you need it. Quality always."
Tagline: Create static websites with the tools you already know and love with HydePHP.

The Hyde Philosophy:
> Developing Hyde sites, and contributing to the framework should be a joy. Great Developer Experience (DX) is our top priority.
> Code should make sense and be intuitive, especially user-facing APIs. Convention over configuration, but not at the expense of flexibility.
> Making sites should not be boring and repetitive - Hyde is all about taking focus away from boilerplate, and letting users focus on the content.

Considerations: While HydePHP targets developers, not all users necessarily are familiar with Laravel, or PHP. So while we want to provide a familiar interface for Laravel/PHP users, we also want to make sure that the API is intuitive and easy to use for all users.
```

### Text generation

Add this to the bottom of your prompts:

```
Remember that the target audience of this document are developers with technical knowledge, though not all will necessarily be familiar with Laravel or PHP.
While it's important to use clear and concise language, you must also make sure to use complete sentences that are easy to read and follow.
The text should not contain marketing speach or buzzwords, nor any unnecessary fluff. Use clear code examples when they add value.
Use headings to group together related sections, with subheadings to make the text easy to skim through.
Also make sure to keep the text engaging and interesting to read!
```
