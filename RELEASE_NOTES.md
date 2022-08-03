## [Unreleased] - YYYY-MM-DD

### About

This update contains **breaking changes** to the internal API regarding page models. This should only affect you directly if you've written any code that interacts with the internal page models, such as constructing them using non-built-in Hyde helpers.

#### Rename slugs to identifiers

Previously internally called `slug(s)`, are now called `identifier(s)`. In all honestly, this has 90% to do with the fact that I hate the word "slug".
I considered using `basename` as an alternative, but that does not fit with nested pages. Here instead is the definition of an `identifier` in the context of HydePHP:

> An identifier is a string that is in essence everything in the filepath between the source directory and the file extension.

So, for example, a page source file stored as `_pages/foo/bar.md` would have the identifier `foo/bar`. Each page type can only have one identifier of the same name.
But since you could have a file with the same identifier in the `_posts` directory, we internally always need to specify what source model we are using.

The identifier property is closely related to the page model's route key property, which consists of the site output directory followed by the identifier. 

### Added
- for new features.

### Changed
- Breaking: Rename AbstractMarkdownPage constructor parameter `slug` to `identifier`
- Breaking: Rename AbstractPage property `slug` to `identifier`
- Breaking: Change `AbstractMarkdownPage` constructor argument positions, putting `identifier` first
- Begin changing references to slugs to identifiers, see motivation above

### Deprecated
- for soon-to-be removed features.

### Removed
- Removed interface MarkdownDocumentContract

### Fixed
- for any bug fixes.

### Security
- in case of vulnerabilities.

