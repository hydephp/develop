## [Unreleased] - YYYY-MM-DD

### About

This update refactors the internal page source model parsing. This will likely not affect you directly, however, if you have written custom code that interacts with any class relating to the PageParser contract, you'll want to take a closer look at the changes.

### Added
- Added a new static shorthand to quickly parse Markdown files into MarkdownDocuments (`MarkdownFileParser::parse()`)
- Added `toArray()` method to MarkdownDocuments, which returns an array of all the body lines

### Changed
- All source model parsing is now handled by the new SourceFileParser action
- Blog post front matter no longer includes merged slug
- MarkdownDocument now implements the `Arrayable` interface
- Markdown page models no longer includes the slug merged into the front matter 
- internal: The DocumentationPage slug now behaves like other pages, and the basename is produced at runtime, see below
- internal: Refactor search index generator to use route system

### Deprecated
- Deprecated `MarkdownDocument::parseFile()`, will be renamed to `MarkdownDocument::parse()`

### Removed
- The PageParserContract interface, and all of its implementations have been removed
- Removed `$localPath` property from DocumentationPage class, see above

### Fixed
- for any bug fixes.

### Security
- in case of vulnerabilities.
