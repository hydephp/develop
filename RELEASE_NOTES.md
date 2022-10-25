## [Unreleased] - YYYY-MM-DD

### About

Keep an Unreleased section at the top to track upcoming changes.

This serves two purposes:

1. People can see what changes they might expect in upcoming releases
2. At release time, you can move the Unreleased section changes into a new release version section.

### Added
- for new features.

### Changed

#### Breaking changes
These are changes that break backwards compatibility and that are likely to concern users using HydePHP to create sites.

- HydePHP now requires PHP 8.1 or higher.

#### Breaking internal changes
These are changes that break backwards compatibility but are unlikely to concern users using HydePHP to create sites.
Instead, these changes will likely only concern those who write custom code and integrations using the HydePHP framework.

These types of changes are handled within the framework ecosystem to ensure they do not affect those using HydePHP to create sites.
For example, if a namespace is changed, all internal references to that namespace are updated, so most users won't even notice it.
If you however have written custom code that explicitly references the old namespace, you will need to update your code to use the new namespace.

- Changes and refactors regarding the internal metadata handling, see below.

### Other changes

- Changes to normalize how navigation priorities are determined.
  - The fallback navigation priority for all navigation items is now 999.
  - The offset for config priorities is now +500 instead of +250 for sidebar navigation.
  * This change may influence the order of navigation items in your site. Before deploying your site to production, you may want to review the order of your navigation items and adjust the priority of any items that are not in the order you want.

### Deprecated
- for soon-to-be removed features.

### Removed
- Removed MetadataItemContract.php (use new abstract class BaseMetadataElement)

### Fixed
- for any bug fixes.

### Security
- in case of vulnerabilities.


---

## Additional details about the internal changes

These are additional details about the changes that are not relevant to the end user, but could be relevant to
developers who write custom code and integrations using the HydePHP framework.

### Changes and refactors regarding the internal metadata handling

The internal metadata handling has been refactored to make it more flexible and easier to extend. If you have not written any custom code that interacts with the metadata system, you can skip this section.

#### Class and namespace changes
 
- The MetadataBag class's namespace has been changed from `Hyde\Framework\Models\Metadata\MetadataBag` to `Hyde\Framework\Modules\Metadata\MetadataBag;`
- All metadata models have been moved to the new namespace `Hyde\Framework\Modules\Metadata\Models`
- All metadata models have been renamed, changing the suffix `Item` to `Model`
