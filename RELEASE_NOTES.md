## [Unreleased] - YYYY-MM-DD

### About

This release contains breaking changes regarding the PostBuildTasks that may require your attention if you have created custom tasks.

### Added
- Added the option to define some site configuration settings in a `hyde.yml` file. See [#449](https://github.com/hydephp/develop/pull/449)
- Build tasks are now automatically registered when placed in the app/Actions directory and end with BuildTask.php

### Changed
- **Breaking changes to build hooks/tasks**:
  - Rename BuildHookService to BuildTaskService
  - AbstractBuildTask::handle and BuildTaskContract::handle now returns null by default instead of void. It can also return an exit code
  - The way auxiliary build actions are handled internally has been changed to use build tasks, see [PR #453](https://github.com/hydephp/develop/pull/453)
- The RSS feed related generators are now only enabled when there are blog posts
  - This means that no feed.xml will be generated, nor will there be any references (like meta tags) to it when there are no blog posts
- The documentation search related generators are now only enabled when there are documentation pages
  - This means that no search.json nor search.html nor any references to them will be generated when there are no documentation pages
- The methods in InteractsWithDirectories.php are now static, this does not affect existing usages
- Renamed HydeSmartDocs.php to SemanticDocumentationArticle.php

### Deprecated
- Deprecated ActionCommand.php as it is no longer used

### Removed
- for now removed features.

### Fixed
- Fixed [#443](https://github.com/hydephp/develop/issues/443): RSS feed meta link should not be added if there is not a feed

### Security
- in case of vulnerabilities.
