## Temp notes:

### Trying it out

Localization is off by default. To try it, set `config/localization.php` to
`'languages' => ['en' => 'English', 'sv' => 'Svenska']`, add a page or two, and run
`php hyde build` or `php hyde serve`.

For a page with genuinely different Swedish content, add a companion source beside
it, so `_pages/about.md` gets `_locales/sv/_pages/about.md`. A page without one
falls back to its canonical content, rendered in Swedish. Interface strings come
from `lang/sv/*.php`, and navigation labels from `lang/sv.json`.

The repository deliberately carries no demo content in `_pages`, `_docs`, or
`_posts`. It used to, and it broke the browser tests: `publish:homepage` refuses to
overwrite a modified `_pages/index.blade.php`, so those tests silently kept serving
the demo homepage, and an extra documentation page shifted the sidebar positions
they assert on. The feature test suite covers the feature instead.

Note that with languages configured, a large part of the unit and feature suite
fails, because the existing tests assert unprefixed route keys. The localization
tests configure their languages per test, so they pass with the committed
configuration, and localize into German so their fixtures cannot collide
with any content the surrounding project has.

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
