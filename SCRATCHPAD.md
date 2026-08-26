## Temp notes:

### Demo setup

Localization is off by default so the test suite stays green. To try the POC, set
`config/localization.php` to `'languages' => ['en', 'sv']` and run `php hyde build`.
You should get `_site/en/`, `_site/sv/`, and an `_site/index.html` redirect to `/en/`.

Note that with languages configured, a large part of the test suite fails, because
the existing tests assert unprefixed route keys. That is expected for now.

### Todo

Document that TranslationServiceProvider must be added to config.php.

Add tests for the localization feature (none exist yet).

### Known gaps

Navigation and the documentation sidebar iterate `Routes::all()` with no language
filter, so an English page lists both the English and the Swedish routes. Fixing
this needs `Localization::currentLanguage()` plus language-aware route queries.

The locale context currently only wraps `$page->compile()`, not `Hyde::shareViewData()`,
so anything resolving translations outside the compile call runs under the default
locale.

`hyde.redirects` and the documentation root redirect are fanned out per language like
any other page, so their destinations are not localized. Decide the URL policy for
the default language before relying on this.

`build:search` builds the search index directly from the page collection rather than
from routes, so it writes the unlocalized path when run standalone.
