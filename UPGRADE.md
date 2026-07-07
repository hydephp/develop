# HydePHP v3.0 Upgrade Guide

## Overview

//

## Before You Begin

### Prerequisites

//

### Backup Your Project

Before starting the upgrade process, it's **strongly recommended** to:

- **Commit all changes to Git** - This allows you to easily revert if needed
- **Create a backup** of your entire project directory
- **Have a previous site build** so you can compare output

If you're not already using Git for version control, now is an excellent time to initialize a repository:

```bash
git init
git add .
git commit -m "Pre-upgrade backup before HydePHP v3.0"
```

### Estimated Time

//

## Step 1: Update Dependencies

### Update Composer Dependencies

//

### Update Node Dependencies

HydePHP v3 upgrades the bundled `vite` dependency from v7 to v8. Update your `package.json` `devDependencies` to require the new major version:

```json
{
    "devDependencies": {
        "vite": "^8.0.0"
    }
}
```

Then run `npm install` (or your package manager's equivalent) to pick up the update.

If you have a custom `vite.config.js` that overrides `build.rollupOptions`, note that Vite 8 builds with Rolldown by default. The `hyde-vite-plugin` now configures its own build options under `build.rolldownOptions` rather than `build.rollupOptions` — if your custom config only sets `rollupOptions`, double check your output still ends up where you expect after upgrading.

## Step 2: Replace the Removed `rebuild` Command

The `rebuild` command has been removed in v3.0. It had no remaining internal consumers now that the realtime compiler renders pages entirely in-memory, and building a single page could silently leave aggregate outputs (sitemap, RSS, search index, navigation) stale while looking like a complete build.

**Before:**
```bash
php hyde rebuild _posts/hello-world.md
```

**After:**

If you need to build a single page programmatically, use `StaticPageBuilder::handle()` directly:

```php
use Hyde\Foundation\Facades\Pages;
use Hyde\Framework\Actions\StaticPageBuilder;

StaticPageBuilder::handle(Pages::getPage('_posts/hello-world.md'));
```

Note that this only produces a correct `_site` when the page is self-contained. For anything that touches aggregate outputs, run `php hyde build` to rebuild the whole site instead.

## Step 3: Replace the Removed Publishing Commands

The three legacy publishing commands (`publish:views`, `publish:configs`, and `publish:homepage`) were removed in v3 and replaced by the unified `publish` command (and, for configuration files, the standard `vendor:publish` path). They are not aliased — invoking one now raises the native "command not found" error, which already suggests `publish` as an alternative.

| Removed in v3              | Use instead                                                                                                                              |
|----------------------------|------------------------------------------------------------------------------------------------------------------------------------------|
| `publish:views`            | `php hyde publish --all` (or `--layouts` / `--components`, or bare `publish` for the interactive picker)                                  |
| `publish:views layouts`    | `php hyde publish --layouts`                                                                                                              |
| `publish:views components` | `php hyde publish --components`                                                                                                          |
| `publish:configs`          | `php hyde vendor:publish --tag=hyde-config --force`                                                                                       |
| `publish:homepage`         | `php hyde publish --page`                                                                                                                 |
| `publish:homepage welcome` | `php hyde publish --page=welcome`                                                                                                        |
| `publish:homepage posts`   | `php hyde publish --page=posts --to=_pages/index.blade.php` (the old command always published to the index; the new default is `_pages/posts.blade.php`) |
| `publish:homepage blank`   | `php hyde publish --page=blank --to=_pages/index.blade.php` (blank now has no default destination)                                        |

The config publish tags were consolidated too: `hyde-configs`, `support-configs`, and `configs` are removed, and `hyde-config` is now the only Hyde config publish tag.

Note that the new `publish` command never overwrites files you have modified without confirmation or `--force`, where the old commands overwrote silently. This is why the `publish:configs` replacement passes `--force` — existing files are skipped without it.

## Migration Checklist

Use this checklist to track your upgrade progress:

- [ ] Replaced any `php hyde rebuild <path>` usage with `StaticPageBuilder::handle()` or a full `php hyde build`
- [ ] Replaced any `publish:views`, `publish:configs`, or `publish:homepage` usage with the new `publish` / `vendor:publish` commands (see the table above; note the `posts`/`blank` `--to` mappings)

## Troubleshooting


## Getting Help

If you encounter issues during the upgrade:

- **Documentation**: [https://hydephp.com/docs/3.x](https://hydephp.com/docs/3.x)
- **GitHub Issues**: [https://github.com/hydephp/hyde/issues](https://github.com/hydephp/hyde/issues)
- **Community Discord**: [https://discord.hydephp.com](https://discord.hydephp.com)

For the complete changelog with all pull request references, see the [full release notes](https://github.com/hydephp/hyde/releases/tag/v3.0.0).
