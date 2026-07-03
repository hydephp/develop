# Welcome to the HydePHP v3 planning document!

Having this document in code lets us know the devlopment state at any given point in the development tree.

## Planned features

## Changes requires to this branch

## Changes required to the v2 branch

---

## Release Notes

### New Features

- Added a generic `--config=key=value` CLI option to the `build` and `rebuild` commands. It's repeatable, so you can override several config values in the same command invocation without editing the project config files, for example `hyde build --config=hyde.pretty_urls=true --config=hyde.features.play_cdn=true`. Overrides take precedence over both the project config and any other build options, such as `--pretty-urls`.

### Feature Changes

### Minor Changes and Cleanup

- Removed the legacy `checkForDeprecatedRunMixCommandUsage` check and the placeholder `--run-dev`/`--run-prod` options from the `build` command, which were kept in v2 only to surface a helpful error message. ([#2461](https://github.com/hydephp/develop/pull/2461))
- Removed the deprecated `hyde.server.dashboard` boolean config check from `DashboardController::enabled()`, which was kept in v2 for backwards compatibility but had since then been replaced with `hyde.server.dashboard.enabled`. ([#2461](https://github.com/hydephp/develop/pull/2462))

### Breaking Changes

### Upgrade guide

Please fill in UPGRADE.md as you make changes.
