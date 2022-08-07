## [Unreleased] - YYYY-MM-DD

### About

This update contains **breaking changes** to the internal API regarding page models. This should only affect you directly if you've written any code that interacts with the internal page models, such as constructing them using non-built-in Hyde helpers.

The update makes large changes to how dynamic data is constructed. Instead of generating page data at runtime, now the data is generated when constructing a page object. This gives the major benefit of being able to see all dynamic data right away, without having to render the page.

### Added
- Added `compile()` method to `Facades\Markdown`, replacing the `parse()` method of the same class
- Adds new actions to handle complex dynamic constructors
- Adds new front matter schema traits to define the public API for front matter and hold their data
- Adds new Meta::link() helper to create `<link>` tags

### Changed
- Breaking: Rename AbstractMarkdownPage constructor parameter `slug` to `identifier`
- Breaking: Rename AbstractPage property `slug` to `identifier`
- Breaking: Change `AbstractMarkdownPage` constructor argument positions, putting `identifier` first
- Breaking: Splits Markdown data from MarkdownDocument into new Markdown model class
- Breaking: The default `config/hyde.php` file now uses `Models\Author` instead of `Helpers\Author`
- Major: Restructure internal page data to use new front matter schema traits 
- Begin changing references to slugs to identifiers, see motivation below
- Makes some helpers in SourceFileParser public static allowing them to be used outside the class

### Deprecated
- Deprecated `Facades\Markdown::parse()`, use `Facades\Markdown::render()` instead
- Deprecated `Facades\Markdown.php`, will be merged into `Models\Markdown.php` 

### Removed
- Removed `Facades\Markdown.php`, merged into `Models\Markdown.php`
- Removed `body()` method from `MarkdownDocumentContract` interface and all its implementations. Use `markdown()->body()` (or cast to string) instead
- Removed `body` property from Markdown pages. Use `markdown()->body()` (or cast to string) instead
- Removed deprecated `Helpers\Author` (fully merged into `Models\Author`, simply swap namespace to upgrade)
- Several internal single-use helper traits have been merged into their respective classes

### Fixed
- for any bug fixes.

### Security
- in case of vulnerabilities.

### Upgrade guide and extra information

#### Rename slugs to identifiers

Previously internally called `slug(s)`, are now called `identifier(s)`. In all honestly, this has 90% to do with the fact that I hate the word "slug".
I considered using `basename` as an alternative, but that does not fit with nested pages. Here instead is the definition of an `identifier` in the context of HydePHP:

> An identifier is a string that is in essence everything in the filepath between the source directory and the file extension.

So, for example, a page source file stored as `_pages/foo/bar.md` would have the identifier `foo/bar`. Each page type can only have one identifier of the same name.
But since you could have a file with the same identifier in the `_posts` directory, we internally always need to specify what source model we are using.

The identifier property is closely related to the page model's route key property, which consists of the site output directory followed by the identifier. 

#### Heavily refactor constructors of Markdown-based page models

Adds a new interface to the Markdown page model constructors, that expects instantiated FrontMatter and MarkdownDocument objects. Normally you would use the SourceFileParser to create the object.

This means that the constructor for all Markdown-based pages is completely changed. To use a format matching the old behaviour, you can use the `MarkdownPageModel::make` method.

#### Title property has been removed from page model constructors

The following syntax has been removed: `new MarkdownPage(title: 'Foo Bar')`
Instead, you can add it with front matter: `MarkdownPage::make(matter: ['title' => 'Foo Bar'])`

#### Markdown pages now have front matter in an object instead of array

This means that instead of the following `$post->matter['title']`, you would use `$post->matter('title')`, which allows you to add a fallback like so: `$post->matter('title', 'Untitled')`

#### Author helper has been merged into the model

The deprecated `Helpers\Author` has been fully merged into `Models\Author`. Simply swap namespaces to upgrade.

```diff
-use Hyde\Framework\Helpers\Author;
+use Hyde\Framework\Models\Author;
```
