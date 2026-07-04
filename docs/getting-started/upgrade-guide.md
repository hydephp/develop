---
navigation:
    label: v3 Upgrade Guide
    priority: 16
---

Will be filled in from UPGRADE.md before release.

## Removed Publishing Commands

The three legacy publishing commands were removed in v3 and replaced by the unified `publish` command
(and, for configuration files, the standard `vendor:publish` path). They are not aliased — invoking one
now raises the native "command not found" error, which already suggests `publish` as an alternative.

<a name="removed-publishing-commands" style="display: inline-block; position: absolute; margin-top: -5rem;"></a>

| Removed in v3             | Use instead                                                                                                                              |
|---------------------------|------------------------------------------------------------------------------------------------------------------------------------------|
| `publish:views`           | `php hyde publish --all` (or `--layouts` / `--components`, or bare `publish` for the interactive picker)                                  |
| `publish:views layouts`   | `php hyde publish --layouts`                                                                                                              |
| `publish:views components`| `php hyde publish --components`                                                                                                          |
| `publish:configs`         | `php hyde vendor:publish --tag=hyde-config --force`                                                                                       |
| `publish:homepage`        | `php hyde publish --page`                                                                                                                 |
| `publish:homepage welcome`| `php hyde publish --page=welcome`                                                                                                        |
| `publish:homepage posts`  | `php hyde publish --page=posts --to=_pages/index.blade.php` (the old command always published to the index; the new default is `_pages/posts.blade.php`) |
| `publish:homepage blank`  | `php hyde publish --page=blank --to=_pages/index.blade.php` (blank now has no default destination)                                        |

The config publish tags were consolidated too: `hyde-configs`, `support-configs`, and `configs` are removed,
and `hyde-config` is now the only Hyde config publish tag.

>info **Behavioral note:** The new `publish` command never overwrites files you have modified without confirmation
> or `--force`, where the old commands overwrote silently. This is the improvement the break buys — and it is why
> the `publish:configs` replacement above passes `--force` (existing files are skipped without it).
