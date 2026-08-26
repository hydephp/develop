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

`hyde.redirects` and the documentation root redirect are fanned out per language
like any other page, so their destinations are not localized. Decide the URL policy
for the default language before relying on this.

Every page is emitted for every language, with no way to mark a page as belonging
to only one language. The hreflang metadata assumes this, as it lists a variant
for every configured language without checking that each one exists.

Language switcher labels are the language codes themselves. There is no place to
configure a display name, so a menu shows `en` and `sv` rather than English and
Svenska.
