## Temp notes:

### Demo setup

Localization is off by default so the test suite stays green. To try it, set
`config/localization.php` to `'languages' => ['en' => 'English', 'sv' => 'Svenska']`
and run `php hyde build` or `php hyde serve`.

The repository carries a small bilingual demo: a home page, about, contact, a
documentation page, and a blog post. All but contact have Swedish companion
sources under `_locales/sv/`, so contact shows the fallback to the canonical
source. Navigation labels come from `lang/sv.json`.

Two things to know when working on this:

Running the test suite deletes the content of `_pages`, `_docs`, and `_posts`,
and rewrites `_pages/index.blade.php` from the vendor welcome page. Restore the
demo with `git checkout` afterwards. The localization tests do not rely on any
of it, and localize into German so their fixtures cannot collide with it.

With languages configured in `config/localization.php`, a large part of the test
suite fails, because the existing tests assert unprefixed route keys. The
localization tests configure their languages per test instead, so they
pass with the committed configuration.

### Deferred

Partial locale coverage, or per-page language availability. Every logical page is
available in every configured language, which is what lets switchers, sitemaps,
hreflang, and navigation work without nullable destinations. An opt-out makes all
of those conditional at once, along with 404 and fallback policy, so they want
solving together as one feature rather than piecemeal.

Note that the hreflang metadata depends on this: it lists a variant for every
configured language without checking that each one exists, which is truthful
only while every route exists in every language.

### Decided, not gaps

Blade pages are localized with Laravel translations and localized partials, not
with companion sources. A Blade page is presentation code rather than authored
prose, and the whole render already runs in the right locale. Making the view
finder prefer a localized view would put a second localization mechanism into
Laravel's own view resolution. Documented as a limitation.

Redirect destinations are relative to the redirect, so a destination naming a page
follows it into its language, one naming a language reaches that language, and an
external one is untouched. Generic unprefixed redirects such as `/about` to
`/en/about` are deliberately not generated; only the webroot redirects.
