## Temp notes:

### Demo setup

Localization is off by default so the test suite stays green. To try the POC, set
`config/localization.php` to `'languages' => ['en', 'sv']` and run `php hyde build`
or `php hyde serve`.

The repository carries a small bilingual demo: a home page, about, contact, a
documentation page, and a blog post. All but contact have Swedish companion
sources under `_locales/sv/`, so contact shows the fallback to the canonical
source. Navigation labels come from `lang/sv.json`.

Two things to know when working on this:

Running the test suite deletes the content of `_pages`, `_docs`, and `_posts`,
and edits `_pages/index.blade.php`. Restore the demo with `git checkout` after.

With languages configured, a large part of the test suite fails, because the
existing tests assert unprefixed route keys. That is expected for now.

### Todo

Document that TranslationServiceProvider must be added to config.php.

Add tests for the localization feature (none exist yet).

### Known gaps

`hyde.redirects` and the documentation root redirect are fanned out per language
like any other page, so their destinations are not localized. Decide the URL policy
for the default language before relying on this.

Every page is emitted for every language, with no way to mark a page as belonging
to only one language. The hreflang metadata assumes this, as it lists a variant
for every configured language without checking that each one exists. Deliberate,
for now: it keeps every route existing in every language, so switcher targets,
sitemap entries, and hreflang all stay truthful while a site is only partly
translated.

Only Markdown pages can be authored per language. A Blade page renders a view
resolved by its identifier, so localizing one needs the view finder to look in
the localized source directory first.

The localized variant reports the canonical source path, so `route:list` and the
dashboard show the canonical file rather than the companion source a language
actually rendered from.

Language switcher labels are the language codes themselves. There is no place to
configure a display name, so a menu shows `en` and `sv` rather than English and
Svenska.
