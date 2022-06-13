# HydePHP - Monorepo Experiment

[![Test & Build](https://github.com/hydephp/develop/actions/workflows/test-build.yml/badge.svg)](https://github.com/hydephp/develop/actions/workflows/test-build.yml)
[![codecov](https://codecov.io/gh/hydephp/develop/branch/master/graph/badge.svg?token=G6N2161TOT)](https://codecov.io/gh/hydephp/develop)

Experimenting with a monorepo. The way I'm thinking is that this repo (when/if transferred to the Hyde org)
is the master origin and source of truth for all HydePHP packages. When a release is tagged here, a CI action
propagates the tag and changes to all* HydePHP packages and creates subsequent releases.

Some packages, like hyde/hyde will need some processing after the merge. For example, the composer.json
should be reverted to not use the dev-master branch.

*Some packages, like HydeFront may not need to have their tags synced. Thinking it is mainly for
hyde/hyde and hyde/framework as they are tightly coupled.

Update: (my notes)
Okay here is where I am at. At this point I don't want to spend a ton of time crafting intricate CI workflows when I don't yet know if the whole monorepo thing is gonna pan out. So my idea is as follows, I'll try using this repo as the source of truth, but as I want the packages to have a git history that makes sense, I will primarly make the "real" commits within them. This version control in this repo will then more function to track and sync states. But actual releases might be handled in the subpackages as I'm not sure how easily I can sync tags and create releases (without adding a ton of complexity at least). However this causes me to still need to make commits in both the subrepositor and main one which is not ideal. I would want to create the commits in the monorepo and then have all those propagated without generic messages like "monorepo commit". This could be remedied by using pull requests to perform merges, and listing all the commits in containing changes for the subrepo. (this worked nice `git log  --since="5 days ago" -- .\packages\framework\ > log`, see https://github.com/hydephp/framework/pull/534)

## Warning

This monorepo project is **highly experimental** and **unstable**! 

## Projects in this monorepo

- Hyde/Hyde (root directory)
- Hyde/Framework (packages/framework)
- Hyde/HydeFront (packages/hydefront)
- Hyde/RealtimeCompiler (packages/realtime-compiler)

### About readonly mirror branches

The readonly mirror branches contain the root repositories that split out from the monorepo.

These branches are still master/development branches and may be unstable.
Changes to them are propagated when the monorepo has pushes, regardless of if the tests pass or not.
