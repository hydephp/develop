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

//

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

## Migration Checklist

Use this checklist to track your upgrade progress:

- [ ] Replaced any `php hyde rebuild <path>` usage with `StaticPageBuilder::handle()` or a full `php hyde build`

## Troubleshooting


## Getting Help

If you encounter issues during the upgrade:

- **Documentation**: [https://hydephp.com/docs/3.x](https://hydephp.com/docs/3.x)
- **GitHub Issues**: [https://github.com/hydephp/hyde/issues](https://github.com/hydephp/hyde/issues)
- **Community Discord**: [https://discord.hydephp.com](https://discord.hydephp.com)

For the complete changelog with all pull request references, see the [full release notes](https://github.com/hydephp/hyde/releases/tag/v3.0.0).
