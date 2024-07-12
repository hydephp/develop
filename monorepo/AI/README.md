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


### Code style

If you need to guide the code style, for example with refactors, use this:

```markdown
Remember to follow our code style:
- Use strict types whenever possible, including closures and arrow functions. Avoid `mixed` types whenever possible. Use PHPDoc annotations to annotate things like array types/shapes and generics. Do not use PHPDocs to annotate types that can be conveyed using native PHP typing.
- Helper methods not part of the public API should be protected, not private. Do extract helper methods for complex parts of code to make it more readable and have each method focus on one responsability. Make sure existing helper methods are named descriptivly.
- Use camelCase for class fields and variables, use snake_case for non-class functions and constants.
- Code comments should be used sparingly, and only when describing things that seem weird but that may be needed to work around quirks out of our control. In general, extracting a self describing helper method is much better.
- Make sure both code and code documentation/comments use proper English grammar and spelling, as well as proper capitalization. Don't remove "todo" comments, but make sure they are formatted like this: "Todo: Do something".
- When writing tests, use PHPUnit where tests are defined using this format: `public function testDoingSomethingDoesSomething()`, we do not specify return types on test methods, but we do when adding testing helper methods.
- PHPDocs should use fully qualified class names, code should import both classes and functions. Function imports are separated with a newline from class imports.
```

### Text generation

Add this to the bottom of your prompts:

```markdown
Remember that the target audience of this document are developers with technical knowledge, though not all will necessarily be familiar with Laravel or PHP.
While it's important to use clear and concise language, you must also make sure to use complete sentences that are easy to read and follow.
The text should not contain marketing speach or buzzwords, nor any unnecessary fluff. Use clear code examples when they add value.
Use headings to group together related sections, with subheadings to make the text easy to skim through.
Also make sure to keep the text engaging and interesting to read!
```

### Proof reading

Add this alongside the document to proofread:

```
Please proofread this documentation to ensure it's clear and contains proper wording, grammar, and formatting. Make sure to use Markdown formatting where it adds value.
Sentences should be easy to read and follow. Use proper grammar and American English. Refrain from using abbreviations. Headings should follow the APA Title Case style.
Do not move around sections or change text that is already clear, as this makes it harder to see your changes.
```
