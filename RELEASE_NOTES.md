## [Unreleased] - YYYY-MM-DD

### About

Keep an Unreleased section at the top to track upcoming changes.

This serves two purposes:

1. People can see what changes they might expect in upcoming releases
2. At release time, you can move the Unreleased section changes into a new release version section.

### Added
- for new features.

### Changed
- Renamed HydeSmartDocs.php to SemanticDocumentationArticle.php
- The RSS feed related generators are now only enabled when there are blog posts
  - This means that no feed.xml will be generated, nor will there be any references (like meta tags) to it when there are no blog posts
- The documentation search related generators are now only enabled when there are documentation pages
  - This means that no search.json nor search.html nor any references to them will be generated when there are no documentation pages

### Deprecated
- for soon-to-be removed features.

### Removed
- for now removed features.

### Fixed
- Fixed [#443](https://github.com/hydephp/develop/issues/443): RSS feed meta link should not be added if there is not a feed 


### Security
- in case of vulnerabilities.
