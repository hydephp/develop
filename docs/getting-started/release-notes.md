---
navigation:
    label: v2 Release Notes
    priority: 15
---

# HydePHP v2.0 Release Notes

## Overview

HydePHP v2.0 represents a major evolution of the framework, introducing significant improvements to the asset system, navigation API, and overall developer experience. This release modernizes the frontend tooling by replacing Laravel Mix with Vite, completely rewrites the navigation system for better flexibility, and introduces numerous performance optimizations throughout the framework.

## Major Features

### 🚀 Modern Frontend Tooling with Vite

We've replaced Laravel Mix with Vite for a faster, more modern development experience:
- **Instant Hot Module Replacement (HMR)** for real-time updates during development
- **Direct asset compilation** into the `_media` folder for cleaner builds
- **Updated build command**: Use `npm run build` instead of `npm run prod` (or `--vite` during the sit build)
- **Vite facade** for seamless Blade template integration
- **Optimized asset serving** through the realtime compiler
- **Hyde Vite plugin** for enhanced integration

### 🎨 Enhanced Asset Management System

The new consolidated Asset API provides a more intuitive interface for handling media files:
- **MediaFile instances** with fluent methods like `getLink()`, `getLength()`, and `getMimeType()`
- **HydeFront facade** for CDN links and Tailwind configuration injection
- **Intelligent caching** with CRC32 hashing for improved performance
- **Automatic validation** to prevent missing assets from going unnoticed
- **Lazy-loaded metadata** for optimal resource usage

### 🧭 Redesigned Navigation API

The navigation system has been completely rewritten for maximum flexibility:
- **YAML configuration support** for defining navigation items
- **Extra attributes** for custom styling and behavior
- **Improved Routes facade** with Laravel-consistent naming conventions
- **Natural priority ordering** using numeric prefixes in filenames
- **Enhanced sidebar management** with better organization options

### 📝 Improved Documentation Features

Documentation pages now benefit from several enhancements:
- **Alpine.js-powered search** with customizable implementation
- **Blade-based table of contents** that's 40x faster than before
- **Custom heading renderer** with improved permalink handling
- **Colored blockquotes** now using Tailwind CSS classes
- **Smart natural language processing** for search headings
- **Dynamic source file links** in Markdown documents

### 🎯 Better Developer Experience

Numerous quality-of-life improvements for developers:
- **PHP 8.4 support** and Laravel 11 compatibility
- **ESM module support** for modern JavaScript development
- **Tailwind CSS v4** with automated upgrade tools
- **Enhanced data collections** with syntax validation
- **Improved error messages** with clearer exception handling
- **Interactive publish:views command** on Unix systems
- **Extension callbacks** with `booting()` and `booted()` methods


## Upgrading to v2.0

**📖 For complete step-by-step upgrade instructions, see the [Upgrade Guide](https://hydephp.com/docs/2.x/upgrade-guide).**

**Important:** PHP 8.2+ is now required. Laravel Mix has been replaced with Vite, and Tailwind CSS has been upgraded to v4.

---

## Breaking Changes

### High Impact Changes

#### 1. Tailwind CSS v4 Upgrade

We've upgraded from Tailwind CSS v3 to v4. Run the automated upgrade tool to migrate your custom classes:

```bash
npx @tailwindcss/upgrade
```

Review the [Tailwind v4 Upgrade Guide](https://tailwindcss.com/docs/upgrade-guide) for detailed breaking changes.

#### 2. ESM Module Migration

Frontend tooling now uses ESM modules instead of CommonJS. If you have custom JavaScript, update to ESM syntax:

**Before:**
```javascript
const module = require('module-name');
module.exports = { /* ... */ };
```

**After:**
```javascript
import module from 'module-name';
export default { /* ... */ };
```

#### 3. Navigation Configuration Format

Update your navigation configuration to use the new array-based format:

**Before:**
```php
'navigation' => [
    'custom_items' => [
        'Custom Item' => '/custom-page',
    ],
],
```

**After:**
```php
'navigation' => [
    'custom_items' => [
        ['label' => 'Custom Item', 'destination' => '/custom-page'],
    ],
],
```

#### 4. Features Configuration

Replace static method calls with enum values in your `config/hyde.php`:

**Before:**
```php
'features' => [
    Features::htmlPages(),
    Features::markdownPosts(),
],
```

**After:**
```php
'features' => [
    Feature::HtmlPages,
    Feature::MarkdownPosts,
],
```

### General Impact Changes

#### Post Author System

The blog post author feature has been significantly improved:

**Configuration changes:**
```php
// Before
'authors' => [
    Author::create('username', 'Display Name', 'https://example.com'),
],

// After
'authors' => [
    'username' => Author::create(
        name: 'Display Name',
        website: 'https://example.com',
        bio: 'Author bio',
        avatar: 'avatar.png',
        socials: ['twitter' => '@username']
    ),
],
```

Key changes:
- Authors are now keyed by username
- `Author::create()` returns an array instead of a `PostAuthor` instance
- `Author::get()` returns `null` if not found (previously created new instance)
- Usernames are automatically normalized (lowercase, underscores for spaces)
- Authors support biographies, avatars, and social media links
- A new `Hyde::authors()` method provides access to all site authors
- Authors can be configured via YAML

The way this system now works is that you first define authors in the config, Hyde the loads this during the booting process, and you can then access them using the get method.

### Medium Impact Changes

#### Asset API Updates

All asset methods now return `MediaFile` instances instead of strings. This instance can be cast to a string which will automatically resolve to a relative link at that time. You can also call helper methods on it. When using Blade templates, thanks to the Stringable implementation no change will happen.

```php
// Methods renamed for clarity
Hyde::asset('image.png');        // Previously: Hyde::mediaLink()
Asset::get('image.png');         // Previously: Asset::mediaLink()
Asset::exists('image.png');      // Previously: Asset::hasMediaFile()
HydeFront::cdnLink('app.css');   // Previously: Asset::cdnLink()
```

Configuration changes:
- Rename `hyde.enable_cache_busting` to `hyde.cache_busting`
- Remove references to `hyde.hydefront_version` and `hyde.hydefront_cdn_url`

#### Routes Facade API

Methods renamed to follow Laravel conventions:

```php
// Before
$route = Routes::get('route-name');        // Returns null if not found
$route = Routes::getOrFail('route-name');  // Throws exception

// After
$route = Routes::find('route-name');       // Returns null if not found
$route = Routes::get('route-name');        // Throws exception
```

#### DataCollection API

- Class renamed from `DataCollections` to `DataCollection`
- Syntax validation now throws `ParseException` for malformed files
- Empty data files are no longer allowed
- Directory creation is no longer automatic
- The `route` function now throws `RouteNotFoundException` if route not found

### Low Impact Changes

#### Includes Facade Return Types

Methods now return `HtmlString` objects:

```blade
{{-- Before: Required unescaped output --}}
{!! Includes::html('partial') !!}

{{-- After: Automatic rendering --}}
{{ Includes::html('partial') }}
```

⚠️ **Security Note:** Output is no longer escaped by default. Use `{{ e(Includes::html('foo')) }}` for user-generated content.

#### Documentation Search Generation

The documentation search page is now generated as an `InMemoryPage` instead of a post-build task, meaning it appears in the dashboard and route list.

#### Sidebar Configuration

Documentation sidebar configuration has been reorganized:
- `docs.sidebar_order` → `docs.sidebar.order`
- `docs.table_of_contents` → `docs.sidebar.table_of_contents`
- `docs.sidebar_group_labels` → `docs.sidebar.labels`

## New Features

### Enhanced Blog Posts

- **Simplified image front matter** with new "caption" field
- **Date prefixes in filenames** for automatic publishing dates
- **Rich markup data** with BlogPosting Schema.org type
- **Author collections** accessible via `Hyde::authors()`
- **Custom posts support** in blog feed component

### Improved Build System

- **Vite integration** with HMR support in realtime compiler
- **Smart asset compilation** - app.js only compiles when needed
- **Environment variable support** for saving previews
- **Grouped progress bars** for InMemoryPage instances
- **Media asset transfers** via dedicated build task

### Developer Tools

- **Interactive publish:views command** on Unix systems
- **Custom HydeSearch.js** support for search customization
- **Extension callbacks** with `booting()` and `booted()` methods
- **Dynamic source file links** in Markdown documents (for example `[Home](/_pages/index.blade.php)`)
- **Filesystem::ensureParentDirectoryExists()** helper method

## Package Updates

### Realtime Compiler
- Simplified asset file locator for media source directory
- Added Vite HMR support
- Experimental Laravel Herd support

### HydeFront
- Complete migration from Sass to Tailwind CSS
- Extracted CSS component partials
- Removed legacy hyde.css file

## Performance Improvements

- **40x faster table of contents generation** using Blade components
- **CRC32 hashing** replaces MD5 for cache busting (much faster)
- **Lazy-loaded media metadata** with in-memory caching
- **Cached media assets** in HydeKernel for instant access

## Dependency Updates

- **PHP**: Now requires 8.2–8.4 (dropped 8.1 support)
- **Laravel**: Upgraded to version 11
- **Tailwind CSS**: Upgraded to version 4
- **Symfony/Yaml**: Updated to version 7
- **Torchlight**: Switched to forked version for compatibility

## Removed Features

### Deprecated Methods
- `PostAuthor::getName()` - use `$author->name` property
- `FeaturedImage::isRemote()` - use `Hyperlinks::isRemote()`
- `DocumentationPage::getTableOfContents()` - use Blade component
- `MarkdownService::withPermalinks()` and `canEnablePermalinks()`

### Build Commands
- `npm run prod` - replaced by `npm run build`
- `--run-dev` and `--run-prod` flags - replaced by `--vite`
- `--run-prettier` flag and Prettier dependency removed

### Configuration Options
- `hyde.hydefront_version` and `hyde.hydefront_cdn_url` - now handled automatically
- `hyde.enable_cache_busting` - renamed to `hyde.cache_busting`
- `hyde.navigation.subdirectories` - renamed to `hyde.navigation.subdirectory_display`

### Components and Files
- `hyde.css` from HydeFront - all styles now in `app.css`
- `table-of-contents.css`, `heading-permalinks.css`, `blockquotes.css` - styles now use Tailwind
- `.torchlight-enabled` CSS class
- `<x-hyde::docs.search-input />` and `<x-hyde::docs.search-scripts />` components - replaced by `<x-hyde::docs.hyde-search />`

## Support & Resources

- **Documentation**: [https://hydephp.com/docs/2.x](https://hydephp.com/docs/2.x)
- **Upgrade Guide**: [https://hydephp.com/docs/2.x/upgrade-guide](https://hydephp.com/docs/2.x/upgrade-guide)
- **GitHub Issues**: [https://github.com/hydephp/hyde/issues](https://github.com/hydephp/hyde/issues)
- **Community Discord**: [https://discord.hydephp.com](https://discord.hydephp.com)

---

For the complete changelog with all pull request references, see the [full changelog](https://github.com/hydephp/hyde/releases/tag/v2.0.0).
