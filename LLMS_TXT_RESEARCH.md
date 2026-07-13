# llms.txt and HydePHP

## Executive summary

`llms.txt` is an emerging, non-IETF proposal for publishing a small, curated, Markdown file at `/llms.txt` so language models and AI agents can find the most important material on a site without trawling full HTML navigation, scripts, and boilerplate. The proposal was published by Jeremy Howard on 3 September 2024 and is maintained publicly through the AnswerDotAI GitHub repository and llmstxt.org, with the authors explicitly inviting community input rather than claiming formal standards status. citeturn5view0turn6view0turn7view0

For a HydePHP site, `llms.txt` is relevant because Hyde sites are often documentation-heavy and static by design: exactly the kind of content where a concise, plain-text, agent-friendly index is useful. The uploaded HydePHP v3 planning document is especially relevant: it says there is currently “no easy way” to add plain-text files like `robots.txt` or `llms.txt`, and it proposes first-class non-HTML pages plus a generated `llms.txt` feature in the v3 branch. That document also shows Hyde’s direction of travel: route-native non-HTML output, generator-backed pages, and user override paths. fileciteturn0file0L11-L18 fileciteturn0file0L29-L31 fileciteturn0file0L515-L529

The practical conclusion is straightforward. For today’s typical static Hyde deployment, the safest approach is a build-time generated root file, ideally from site metadata and per-page front matter. For a future Hyde v3-style implementation, a first-class `llms.txt` page is cleaner because it participates in routing, build manifests, local serve, and extension points. A fully dynamic endpoint is possible, but it is a poor fit for “typical static hosting” unless you deliberately add an edge/serverless layer. fileciteturn0file0L5-L7 fileciteturn0file0L48-L55

`llms.txt` should not be confused with a control plane. It does **not** replace `robots.txt`, access control, authentication, or contractual/licensing terms. The llmstxt.org proposal frames it as an inference-time aid, while `robots.txt` remains an advisory crawler-access mechanism and `security.txt` remains a separate RFC-backed disclosure-contact format. If you care about blocking or monetising AI crawlers, you still need crawler controls, logs, and enforcement outside `llms.txt`. citeturn7view0turn12view0turn13view0turn20view0

## What llms.txt is and where it comes from

The official llmstxt.org proposal describes `llms.txt` as a Markdown file placed at `/llms.txt` to “provide information to help LLMs use a website at inference time”. Its rationale is that LLMs increasingly rely on website information, but full websites are often too large and noisy for context windows, whereas a single concise, curated file can point them to the right materials. citeturn5view0turn7view0

The primary sources worth bookmarking are the llmstxt.org proposal, the public AnswerDotAI GitHub repository, the `llms-txt` parser/CLI documentation, and the HydePHP planning note you supplied. The llmstxt.org page names Jeremy Howard as author and 3 September 2024 as publication date, while the site’s “Next steps” section says the specification is open for community input and points to the GitHub repository and Discord for discussion. citeturn5view0turn7view0turn6view0

Two clarifications matter:

First, `llms.txt` is a **proposal** and an emerging convention, not a formal web standard. The proposal language is explicit, and there is no RFC or W3C Recommendation defining it. By contrast, `robots.txt` is standardised in RFC 9309 and `security.txt` is defined in RFC 9116. citeturn5view0turn12view0turn25view0

Second, the proposal is intentionally narrow. It does not prescribe how an agent must process the file; it says processing depends on the application. That means adoption can be useful even before universal support exists, but it also means interoperability is partly conventional rather than guaranteed. citeturn6view0turn7view0

For HydePHP specifically, the uploaded v3 draft is unusually aligned with the proposal. It explicitly calls out `robots.txt` and `llms.txt` as target outputs, says non-HTML pages should become first-class pages rather than post-build side effects, and sketches a generated `llms.txt` action with route links, page titles, and descriptions/abstracts. It also emphasises that the default enable/disable decision should be deliberate because some users will have privacy or OPSEC concerns. fileciteturn0file0L5-L7 fileciteturn0file0L29-L31 fileciteturn0file0L515-L529

## Format, syntax, and how it differs from robots.txt and security.txt

The llmstxt.org format is Markdown-based. The proposal says a conforming file is normally located at `/llms.txt` and contains, in order: an optional BOM, a single H1 with the project/site name, a blockquote summary, optional free-form non-heading detail sections, and then zero or more H2 sections containing lists of links. Each list item contains a required Markdown link and may optionally add a colon and short description. The only required element is the H1. An H2 section named `Optional` has special semantics: agents may skip it when they need a shorter context. citeturn7view0

The proposal also recommends, where useful, that pages expose clean Markdown versions by appending `.md` to the page URL, because Markdown is easier for models to consume than HTML. This is important for documentation sites, but it is a recommendation, not a hard dependency. In practice, implementations vary: Cloudflare heavily uses Markdown-addressable pages and even tells agents to prefer the Markdown version or `Accept: text/markdown`, whereas Vercel’s `llms.txt` points at ordinary documentation URLs and separately advertises `llms-full.txt`. citeturn6view0turn20view0turn20view1turn15view0

The following comparison summarises the practical differences. It is derived from the llmstxt.org proposal, RFC 9309, RFC 9116, and Google’s robots guidance. citeturn7view0turn12view0turn13view0turn25view0turn13view2

| File | Primary purpose | Status | Typical location | Structure | Enforcement |
|---|---|---|---|---|---|
| `llms.txt` | Curated guidance and content discovery for LLMs/agents | Proposal / emerging convention | `/llms.txt` | Markdown with H1, blockquote, H2 link sections | None by itself |
| `robots.txt` | Advisory crawler access rules | IETF Proposed Standard, RFC 9309 | `/robots.txt` | Line-oriented rules such as `User-agent`, `Allow`, `Disallow` | Voluntary crawler compliance |
| `security.txt` | Vulnerability disclosure contacts and policy | IETF RFC 9116 informational RFC | `/.well-known/security.txt` preferred; root allowed for legacy compatibility | Field/value records such as `Contact`, `Expires`, `Canonical` | Informational, but formally specified |

A few practical consequences flow from this comparison.

`robots.txt` is about crawler access, not explanation. Google says it tells search engine crawlers which URLs they can access, mainly to manage crawl load, and also warns that it is **not** a mechanism for keeping a page out of Google by itself. RFC 9309 likewise says the rules are not a form of access authorisation. citeturn13view0turn13view1turn12view0

`security.txt` is about security disclosure contacts, not AI guidance. RFC 9116 requires a machine-parsable text file, mandates an `Expires` field, recommends HTTPS, defines specific fields such as `Contact`, `Canonical`, and `Preferred-Languages`, and assigns a well-known URI. citeturn13view2turn13view3turn13view4turn25view3turn25view4

`llms.txt` is closer to a curated documentation index than to either control file. It is unusual in using Markdown instead of a classic structured format because the proposal expects the file itself to be read directly by language models and agents. citeturn7view0

An illustrative `llms.txt` for a Hyde site might look like this:

```txt
# Example HydePHP Site

> Official documentation and articles for Example HydePHP Site. Public content only. Prefer the Docs and Reference sections before browsing the wider site.

This file is a curated guide for language models and AI agents. Link descriptions are authoritative summaries maintained by the site team.

## Docs
- [Getting started](https://example.com/docs/getting-started.md): Installation, local development, and first build.
- [Content authoring](https://example.com/docs/authoring.md): Markdown, front matter, Blade, and assets.
- [Deployment](https://example.com/docs/deployment.md): Static hosting, CDN, cache invalidation, and previews.

## Reference
- [Configuration](https://example.com/docs/configuration.md): Site configuration keys and defaults.
- [Extensions](https://example.com/docs/extensions.md): Custom generators, hooks, and extension points.

## Blog
- [Release notes](https://example.com/blog/releases): Product changes and upgrade notes.
- [Architecture notes](https://example.com/blog/architecture): Design decisions and implementation rationale.

## Optional
- [About](https://example.com/about): Project overview and maintainers.
- [Privacy policy](https://example.com/privacy): Public privacy notice.
```

That sample follows the official ordering and the special `Optional` convention from llmstxt.org. citeturn7view0

## Adoption, common use cases, and implications

Adoption is now real enough to matter for documentation tooling, even though the standard remains informal. The llmstxt.org site itself lists integrations including a Python parser/CLI, a JavaScript implementation, a VitePress plugin, a Docusaurus plugin, a Drupal recipe, and a PHP library. Public directories also list many deployed `llms.txt` files and many `llms-full.txt` companions across documentation sites and commercial domains. citeturn5view0turn7view1turn22view2turn22view3turn22view4turn22view0turn19view0turn19view2

Among major companies and platforms, the strongest primary-source examples are documentation ecosystems:

Cloudflare publishes a top-level documentation `llms.txt`, many product-level `llms.txt` files, and corresponding `llms-full.txt` resources. Its docs also explicitly instruct agents to fetch Markdown instead of HTML and to use `llms.txt` as the documentation index. citeturn14view0turn20view0turn20view1turn26view3

Stripe serves `docs.stripe.com/llms.txt`, and its content is clearly agent-oriented: it includes operational guidance such as checking package registries for current versions rather than trusting memorised version numbers. That is a useful signal that `llms.txt` is already being used for more than a passive site map. citeturn14view1

Vercel serves `vercel.com/llms.txt` and prominently points to a `llms-full.txt` file containing its full documentation content. Anthropic’s developer documentation also exposes a root `llms.txt`, and Meta’s Horizon developer docs publish a large documentation index in `llms.txt` form. citeturn15view0turn26view2turn15view1turn24view0

The most common use cases are therefore documentation discovery, curated agent context, and easier retrieval for AI assistants. The llmstxt.org proposal itself emphasises developer documentation, software APIs, e-commerce/product explanation, legislation, personal websites, and institutional information. citeturn5view0turn6view0

The security, privacy, and legal picture is more modest than the marketing around the concept sometimes suggests.

`llms.txt` is public by design. It should therefore contain only public URLs and public summaries; do not place secrets, unpublished materials, admin paths, preview URLs, or anything whose mere disclosure would be sensitive. This is an inference from the proposal’s public-root-file design and the general limitations shared with advisory text files. citeturn7view0turn12view0turn13view0

It is not an enforcement mechanism. If your aim is “do not crawl this” or “do not train on this”, `llms.txt` alone is too weak. The llmstxt.org proposal frames the file mainly as an inference-time aid; RFC 9309 and Google both make clear that even `robots.txt` is only advisory and not authorisation or robust secrecy. Cloudflare’s AI Crawl Control documentation reinforces this by offering separate monitoring, allow/block policies, robots compliance tracking, and even monetisation for AI crawling. citeturn7view0turn12view0turn13view0turn20view0

Legally, because no jurisdiction was specified, the safest general position is this: treat `llms.txt` as notice and guidance, not as a substitute for terms, licences, authentication, rate limits, or contractual controls. If you need stronger legal signalling, put the real terms in your existing legal documents and link them as public resources; if you need stronger technical control, use robots directives, auth, WAF/CDN controls, logging, and bot management. That conclusion is an inference from the proposal’s non-standard status and the enforcement limits of comparable files. citeturn5view0turn12view0turn25view0turn20view0

## HydePHP implementation strategy

The exact HydePHP version was unspecified, so the right recommendation depends on whether you are working with current static-site workflows or the v3 draft direction in the uploaded planning note. That note says the v3 branch wants non-HTML outputs like `robots.txt`, `llms.txt`, sitemap, RSS, and JSON pages to become first-class pages in routing and the build pipeline, and it proposes a generated `llms.txt` feature with route grouping and exclusions. fileciteturn0file0L1-L7 fileciteturn0file0L19-L31 fileciteturn0file0L515-L529

```mermaid
flowchart LR
    A[Hyde content and front matter] --> B[llms metadata selection]
    B --> C[Generator or Blade template]
    C --> D[/llms.txt in build output]
    D --> E[Static host or CDN]
    E --> F[LLM or agent fetches /llms.txt]
    F --> G[Follows curated links]
    G --> H[Markdown pages if available]
```

The key architectural choice is when generation happens.

A build-time file is the best default for a typical Hyde deployment because it works everywhere Hyde does: local builds, GitHub Pages, Netlify, Cloudflare Pages, S3-style static hosting, and CDN-only stacks. A route-native page is cleaner, but only if your Hyde version actually supports non-HTML pages cleanly. The uploaded draft suggests Hyde v3 aims to make that the canonical approach. fileciteturn0file0L11-L18 fileciteturn0file0L48-L55

The following table compares the realistic options for HydePHP. It synthesises the llmstxt.org requirements with the HydePHP planning note and the static-hosting assumption. citeturn7view0 fileciteturn0file0L11-L18 fileciteturn0file0L184-L200

| Option | Static-hosting fit | Main advantages | Main drawbacks | Best use |
|---|---|---|---|---|
| Hand-maintained root file | Excellent | Simple, no framework coupling | Easy to drift out of date | Small brochure/blog sites |
| Build-time generated file | Excellent | Deterministic, CI-friendly, no runtime needed | Requires custom generator logic | Best default for most Hyde sites |
| Metadata-driven Blade/template at build | Excellent | Editors can steer sections/descriptions via front matter | Needs template + generation step | Docs/blog sites with rich metadata |
| Route-native generated page | Good if Hyde version supports non-HTML pages | Clean routing, local serve, extension hooks, easier override story | Depends on Hyde version/branch capability | Future-facing Hyde v3 style |
| Dynamic endpoint | Poor for typical static hosting | Always current, easy runtime filtering | Adds runtime infrastructure, cache complexity, less “static” | Only when you already run edge/serverless/PHP |

A sensible application-level config file might look like this. This is **illustrative**, not current Hyde core API:

```php
<?php

return [
    'enabled' => env('HYDE_LLMS_ENABLED', true),

    'title' => env('APP_NAME', 'Example HydePHP Site'),
    'summary' => config('hyde.description', ''),
    'details' => 'Public documentation and articles for this site. Prefer Docs and Reference before wider browsing.',
    'base_url' => config('hyde.url'),

    'prefer_markdown_urls' => true,

    'sections' => [
        'Docs' => ['docs/*'],
        'Reference' => ['reference/*'],
        'Blog' => ['posts/*'],
        'Project' => ['about', 'changelog'],
    ],

    'exclude' => [
        '404',
        'search.json',
        'sitemap.xml',
        'feed.xml',
        'robots.txt',
        'llms.txt',
        'media/*',
        'drafts/*',
    ],

    'optional' => [
        'privacy',
        'terms',
        'contact',
    ],
];
```

A metadata-driven Blade template is a good middle path because it lets maintainers curate output without hand-editing the final text:

```php
{{-- resources/views/llms-txt.blade.php --}}
# {{ $title }}

> {{ $summary }}

@if (!empty($details))
{{ $details }}
@endif

@foreach ($sections as $sectionName => $links)
## {{ $sectionName }}

@foreach ($links as $link)
- [{{ $link['title'] }}]({{ $link['url'] }})@if (!empty($link['description'])): {{ $link['description'] }}@endif
@endforeach

@endforeach
```

A simple generator service can then render that template and write the final file during build:

```php
<?php

use Illuminate\Support\Facades\Blade;

final class GenerateLlmsTxt
{
    public function generate(array $sections, array $config): string
    {
        return Blade::render('llms-txt', [
            'title' => $config['title'],
            'summary' => $config['summary'],
            'details' => $config['details'] ?? '',
            'sections' => $sections,
        ]);
    }
}
```

If your Hyde version still treats non-HTML outputs as awkward post-build artefacts, write `_site/llms.txt` after the page collection has been built. If Hyde v3 lands as described in the planning note, the cleaner design is a first-class page that compiles lazily and resolves its generator from the container, matching the draft’s approach for generated non-HTML pages. fileciteturn0file0L15-L27 fileciteturn0file0L184-L200

A future-facing v3-style sketch would look roughly like this:

```php
<?php

use Hyde\Pages\InMemoryPage;

final class LlmsTxtPage extends InMemoryPage
{
    public static string $outputExtension = '.txt';

    public function __construct()
    {
        parent::__construct('llms.txt');
    }

    public function compile(): string
    {
        return app(GenerateLlmsTxt::class)->generateFromSite();
    }
}
```

And registration would ideally happen through Hyde’s boot/extension mechanism, because the planning note explicitly points to `Hyde::kernel()->booting()` callbacks and `HydeExtension` as the user-land extension points, and says user-defined pages should beat framework-generated ones when route keys collide. fileciteturn0file0L53-L57 fileciteturn0file0L223-L254

For a dynamic endpoint, only use it if you knowingly accept non-static infrastructure. An illustrative PHP route might be:

```php
<?php

Route::get('/llms.txt', function () {
    $content = app(GenerateLlmsTxt::class)->generateFromSite();

    return response($content, 200, [
        'Content-Type' => 'text/plain; charset=utf-8',
        'Cache-Control' => 'public, max-age=300',
    ]);
});
```

That approach is technically fine, but it is not a natural fit for the assumed “typical static hosting” environment.

## Deployment, validation, interoperability, and recommended defaults

Operationally, the most important serving rule is simple: publish a UTF-8 plain-text file at the root path `/llms.txt`. The official proposal targets that location, and major implementations from Cloudflare, Stripe, Vercel, Anthropic, and Meta all expose plain-text root or documentation-index files that way. Hyde’s v3 note is also helpful here because it says the realtime compiler already maps `.txt`, `.xml`, and `.json` output paths to correct content types. citeturn7view0turn14view0turn14view1turn14view2turn15view1turn24view0 fileciteturn0file0L48-L49

On static hosting and CDNs, treat `llms.txt` like any other generated public artefact: publish it at the origin root, cache it normally, and purge/invalidate it whenever docs or key pages change. Because the file is small and low-risk, short-to-moderate cache TTLs are usually more useful than aggressive long-lived caching; the value of the file depends on freshness. That is an implementation recommendation rather than a standard requirement.

Interoperability is currently best thought of in layers.

At the broadest layer, old browsers and legacy crawlers are unaffected: `llms.txt` is just another public text file, so publishing it is backwards-compatible by default. citeturn7view0

At the agent layer, support is uneven but improving. Some vendors and docs platforms clearly use it operationally, and tooling exists to parse it, generate context bundles, and validate structure. The official Python tooling parses the file and can expand it into XML context for models such as Claude; the PHP library supports creating, reading, and validating files; and ecosystem plugins generate both `llms.txt` and, in some cases, `llms-full.txt`. citeturn7view1turn22view0turn22view2turn22view3

At the control layer, do not assume compliance. If you need to manage AI crawlers, pair `llms.txt` with `robots.txt`, logs, and enforcement. Cloudflare’s AI Crawl Control is a good illustration: it separately tracks AI crawler activity, allows/block rules, monitors robots compliance, and can monetise crawl access. citeturn20view0turn20view1

For HydePHP sites, the recommended default content is conservative:

Use the site/project name as the H1. Use the Hyde description as the summary. Provide one short explanatory paragraph if needed. Then publish a small number of sections such as `Docs`, `Reference`, `Blog`, and `Optional`. Within those sections, prefer stable, canonical pages with strong titles and strong descriptions. Exclude search indexes, feeds, sitemaps, tag archives, pagination artefacts, media files, drafts, previews, and machine-only outputs. The HydePHP planning note explicitly associates `llms.txt` generation with page titles and documentation abstracts, which is exactly the right default. citeturn7view0turn6view0 fileciteturn0file0L519-L524

A good maintainer checklist is therefore:

- Confirm that `/llms.txt` exists at the site root and is served as plain text. citeturn7view0turn14view0turn14view1
- Keep exactly one H1 and use the canonical project/site name. citeturn7view0
- Include a short summary blockquote and concise link descriptions. citeturn7view0
- Prefer Markdown or other clean text targets where available; otherwise use stable canonical URLs. citeturn6view0turn20view0turn15view0
- Exclude private, draft, preview, search-index, feed, sitemap, and other non-user-facing outputs. fileciteturn0file0L148-L166
- Pair the file with `robots.txt`, access control, and bot management where policy matters. citeturn13view0turn20view0
- Rebuild and purge caches whenever key content changes.
- Validate the structure in CI before deployment. citeturn22view0turn7view1

For automated tests, I would add both content tests and behaviour tests. The llmstxt.org guidance explicitly recommends expanding the file into an LLM context file and then testing models against real questions. The official parser/CLI and the PHP library make that easy. citeturn7view0turn7view1turn22view0

A practical PHPUnit-style test might be:

```php
<?php

use Stolt\LlmsTxt\LlmsTxt;

final class LlmsTxtTest extends TestCase
{
    public function test_llms_txt_is_generated_and_valid(): void
    {
        $path = base_path('_site/llms.txt');

        $this->assertFileExists($path);

        $txt = file_get_contents($path);
        $this->assertStringStartsWith('# ', $txt);
        $this->assertStringContainsString('## Docs', $txt);
        $this->assertStringNotContainsString('/drafts/', $txt);
        $this->assertStringNotContainsString('search.json', $txt);

        $parsed = (new LlmsTxt())->parse($path);
        $this->assertTrue($parsed->validate());
    }
}
```

I would also add a link-check test that every URL listed in `llms.txt` resolves successfully after build, plus a snapshot/regression test to catch accidental churn in headings, section names, or excluded paths. For a higher-level test, run the generated file through `llms_txt2ctx` and verify that a model or parser can answer a few deterministic site questions from the expanded context. citeturn7view1turn22view0