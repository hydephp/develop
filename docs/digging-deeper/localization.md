---
navigation:
    label: "Localization"
    priority: 28
---

# Localization

## Introduction

Hyde can compile your site once for each language you want it to be available in, placing each
version in a subdirectory named after its language. Interface text is translated with Laravel's
standard translation system, and pages that need genuinely different content in a language can
be authored separately for it.

The model is that a page is authored once and rendered many times. A source file is always
exactly one page, no matter how many languages your site has. The languages are variants of
its route, so `_pages/about.md` stays one page, and becomes `/en/about.html` and
`/sv/about.html` when your site is available in English and Swedish.

## Configuration

Localization is opt-in, and is enabled by listing the languages your site should be available
in, in `config/localization.php`. The first language listed is the default language.

```php
// filepath: config/localization.php
'languages' => [
    'en',
    'sv',
],
```

Languages can also be given display names, which the language switcher will show instead of the
language code:

```php
// filepath: config/localization.php
'languages' => [
    'en' => 'English',
    'sv' => 'Svenska',
],
```

Leaving the array empty disables localization entirely, and your site is compiled exactly as it
would be without the feature, with pages placed in the site webroot.

>info Localization requires the `TranslationServiceProvider` to be registered in your `app/config.php`. See the [upgrade guide](upgrade-guide) if you are upgrading an existing project.

### What this changes

With two languages configured, every page is compiled into both:

```text
_site/
├── index.html          # Redirects to the default language
├── en/
│   ├── index.html
│   └── about.html
└── sv/
    ├── index.html
    └── about.html
```

Route keys are prefixed the same way, so the route key of `_pages/about.md` becomes `en/about`
in English and `sv/about` in Swedish. Referring to a page by its unprefixed route key still
works, and resolves to the page of the language currently being rendered, so an English
page linking to `about` links to the English one, and a Swedish page to the Swedish one.

The site webroot gets a redirect to the default language, so a visitor arriving at `/` is sent
to `/en/`.

## Translating interface text

Translation strings are loaded from the `lang` directory in your project root, using Laravel's
standard translation system, and are resolved for the language of the page being compiled.

```php
// filepath: lang/sv/main.php
return [
    'welcome' => 'Hej Världen!',
];
```

```blade
<h1>{{ __('main.welcome') }}</h1>
```

Everything involved in rendering a page runs in the language it is being compiled for, so
translations resolve correctly in your pages, layouts, components, and view composers alike.

### Navigation labels

Navigation menu labels are passed through the translation helper, so you can translate a menu by
adding a translation string matching the label. Labels without a matching string are shown as
they are.

```json
// filepath: lang/sv.json
{
    "Home": "Hem",
    "About": "Om oss"
}
```

## Translating page content

Interface strings work well for short pieces of text, but not for the body of an article. When a
page needs genuinely different content in a language, give it a companion source file under the
`_locales` directory, in a path mirroring the page's own.

```text
_pages/about.md                 # The canonical source
_locales/sv/_pages/about.md     # The Swedish content for it

_posts/hello-world.md
_locales/sv/_posts/hello-world.md

_docs/getting-started.md
_locales/sv/_docs/getting-started.md
```

The companion file supplies both the body and the front matter of that language's version, so
each language can have its own title, description, and everything else you set in front
matter.

```markdown
// filepath: _locales/sv/_pages/about.md
---
title: Om oss
---

# Om oss

Vi bygger statiska webbplatser med HydePHP.
```

Companion files are not discovered as pages of their own, as they live outside the page source
directories. The canonical source remains the identity of the page.

### Falling back to the canonical content

A language does not need a companion file for every page. When one is missing, the page is
rendered from its canonical source instead, in that language.

| Canonical source | Companion source | Result                                     |
|------------------|------------------|--------------------------------------------|
| exists           | exists           | the companion source is rendered           |
| exists           | missing          | the canonical source is rendered           |

Only the content falls back. The page is still compiled for its own language, so its navigation,
interface strings, URLs, and metadata are all localized as usual. That means you can translate
a site a page at a time, and no page ever goes missing from a language while its translation
is still outstanding.

## The language switcher

A language switcher is included in the main navigation menu and the documentation sidebar. It
links to the same page in each of your languages, and shows the display name of each one, or
its language code when no name is configured.

It renders nothing when your site has fewer than two languages, so it costs a site that is not
localized nothing.

## Search engine metadata

Each page declares the language it was compiled for on its `html` element, and its own canonical
URL. Pages also link to every language version of themselves with `hreflang` metadata, so that
search engines serve visitors the right one instead of treating the versions as duplicates of
each other. An `x-default` entry points at the site webroot, which redirects to your default
language.

This requires absolute URLs to be meaningful, so it is only included when your site has a
configured site URL.

## Documentation search

Documentation search indexes are generated for each language, and contain the pages of that
language only, so searching the Swedish documentation returns Swedish results.

## Redirects

Redirect destinations are relative to the redirect itself, which means a destination naming
another page follows the redirect into its own language.

```php
// filepath: config/hyde.php
'redirects' => [
    'old-about' => 'about',
],
```

This sends `/en/old-about.html` to `/en/about` and `/sv/old-about.html` to `/sv/about`.

A destination naming a language explicitly always reaches that language, regardless of which one
the redirect was followed from, and external destinations are left exactly as they are.

## Limitations

**Blade page templates use Laravel's normal localization system.** Companion source files are for
Markdown-backed content pages. A Blade page is presentation code rather than authored prose, so
translate it with the `__()` helper and localized partials, as you would in any Laravel
application.

**Every logical page is available in every configured language.** There is currently no way to
mark a page as belonging to only some of your languages. Pages without a companion source for
a language fall back to their canonical content rather than being left out.
