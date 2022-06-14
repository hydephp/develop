# HydePHP - Monorepo Experiment
## Cutting Edge - And quite possibly rather unstable 

[![Test & Build](https://github.com/hydephp/develop/actions/workflows/test-build.yml/badge.svg)](https://github.com/hydephp/develop/actions/workflows/test-build.yml)
[![codecov](https://codecov.io/gh/hydephp/develop/branch/master/graph/badge.svg?token=G6N2161TOT)](https://codecov.io/gh/hydephp/develop)

Experimenting with a monorepo. The way I'm thinking is that this repo (when/if transferred to the Hyde org)
is the master origin and source of truth for all HydePHP packages. When a release is tagged here, a CI action
propagates the tag and changes to all* HydePHP packages and creates subsequent releases.

Some packages, like hyde/hyde will need some processing after the merge. For example, the composer.json
should be reverted to not use the dev-master branch.

*Some packages, like HydeFront may not need to have their tags synced. Thinking it is mainly for
hyde/hyde and hyde/framework as they are tightly coupled.

## Warning

This monorepo project is **highly experimental** and **unstable**!
Changes pushed to the actual package repositories are only made when stable.

## Projects in this monorepo

- Hyde/Hyde (root directory)
- Hyde/Framework (packages/framework)
- Hyde/HydeFront (packages/hydefront)
- Hyde/RealtimeCompiler (packages/realtime-compiler)

### About readonly mirror branches

The readonly mirror branches contain the root repositories that split out from the monorepo.

These branches are still master/development branches and may be unstable.
Changes to them are propagated when the monorepo has pushes, regardless of if the tests pass or not.
