# Internal code definitions and naming conventions for framework developers

In an attempt to reduce redundant PHPDoc comments within the internal code I thought it could be useful to write definitions for the various terms I have been using.

If you have ideas for improvement, additions, clarifications, or anything else, please raise an issue on the [DocsCI Repo](https://github.com/hydephp/DocsCI)!

## Common variable names and their meaning
**$matter**: YAML Front Matter, usually parsed into an array.
**$body**: The main content of a document, usually in the form of unparsed Markdown
**$title**: The string used to set the HTML title in compiled pages.
**$slug**: The basename of a file. See the chapter "Anatomy of a File Name" for more. A slug never has a file extension and does not contain any path information (prefixes).
Example: `hello-world`
**$filename**: The full name of a file (without path information). In other words, it's a slug with a file extension.
Example: `hello-world.md`
**$filepath**: The full file path (almost always relative to the root of the Hyde project installation). Includes the file extension.
Example: `_posts/hello-world.md`

## Deep dives

### Anatomy of a File Name
Hyde makes frequent references to file names. Here are some visualizations of the different parts of a file name.
 
#### Slug (Basename)
_posts/**hello-world**.md

#### File Name
_posts/**hello-world.md**

#### File Path
**_posts/hello-world.md**
