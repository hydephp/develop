## [Unreleased] - YYYY-MM-DD

### About

Keep an Unreleased section at the top to track upcoming changes.

This serves two purposes:

1. People can see what changes they might expect in upcoming releases
2. At release time, you can move the Unreleased section changes into a new release version section.

### Added
- for new features.

### Breaking changes

#### Abstract

This beta release contains a plethora of breaking changes compared earlier beta versions.
So many in fact, it could actually be easier and faster to recreate your project from scratch than to upgrade a particularly complex project. Though it only took me like five minutes to upgrade a simple documentation site, see [this diff](https://github.com/caendesilva/hyde-example-documentation-site/commit/f647f9250ecb20cf7bbf43bb10cd6401fae201cb) to see what I did.

The good news however, is that as HydePHP approaches version 1.0, there will no longer be releases like these with breaking changes.

While I've got your attention: read this the section right after this, as you might not need to make any changes at all.

#### Do I need to make any changes to my project?

If any of these statements are true, you will probably need to make changes to your project, and it might be easiest to copy over your content to a new project.

- You currently only have PHP 8.0 installed, HydePHP now requires PHP 8.1.
- You have written custom code (for example in Blade views) that relies on the old API.
- You have published the built-in Blade views (you should be able to get away by just republishing them).

In all cases, you will most definitely need to republish the configuration files and update the `app/bootstrap.php` file.

#### Major breaking changes

These are changes that break backwards compatibility and that are likely to concern users using HydePHP to create sites.

- HydePHP now requires PHP 8.1 or higher.
- Almost all namespaces in the framework have been changed and restructured.

#### Breaking internal changes
These are changes that break backwards compatibility but are unlikely to concern users using HydePHP to create sites.
Instead, these changes will likely only concern those who write custom code and integrations using the HydePHP framework.

These types of changes are handled within the framework ecosystem to ensure they do not affect those using HydePHP to create sites.
For example, if a namespace is changed, all internal references to that namespace are updated, so most users won't even notice it.
If you however have written custom code that explicitly references the old namespace, you will need to update your code to use the new namespace.

- The Framework package now uses strict types for its source files.

### Changed

- for changes in existing functionality.

### Deprecated
- for soon-to-be removed features.

### Removed
- for now removed features.

### Fixed
- for any bug fixes.

### Security
- in case of vulnerabilities.
