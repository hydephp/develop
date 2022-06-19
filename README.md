# HydePHP - Monorepo Experiment
## Cutting Edge - And quite possibly rather unstable 

[![Test & Build](https://github.com/hydephp/develop/actions/workflows/continuous-integration.yml/badge.svg)](https://github.com/hydephp/develop/actions/workflows/continuous-integration.yml)
[![Framework Tests (Matrix)](https://github.com/hydephp/framework/actions/workflows/run-tests.yml/badge.svg)](https://github.com/hydephp/framework/actions/workflows/run-tests.yml)
[![Hyde Tests](https://github.com/hydephp/hyde/actions/workflows/run-tests.yml/badge.svg)](https://github.com/hydephp/hyde/actions/workflows/run-tests.yml)
[![codecov](https://codecov.io/gh/hydephp/develop/branch/master/graph/badge.svg?token=G6N2161TOT)](https://codecov.io/gh/hydephp/develop)

## How the monorepo currently works

Changes to HydePHP including (m)any of the first-party packages are made here.
Once pushed two important CI jobs kick in, one for testing, and one for splitting the monorepo.

The monorepo is split in three* stages to decouple the process.

1. **Packaging** where we create action artifacts containing the package
2. **Splitting** where we upload the artifact to the corresponding readonly mirror branch.
3. **Pushing**   where we push the contents of the mirror branches to the develop branches of their repositories

*The Hyde/Hyde project is stored in the monorepo root and works a bit differently. Here don't package the data, instead we remove monorepo code and apply persisted data before running the next steps.

The release cycle for Hyde/Hyde and Hyde/Framework are synced. Before creating the release we merge the packages' develop branches into their master branches.
Note that I'm just now testing out this release system, and it is entirely possible that we'll just keep minor versions in sync, and not bother with patches the framework changes much more frequently than hyde, leading to a bunch patch releases where there are no actual changes.

## Warning

This monorepo project is still new, and the internal structure of it may be changed without notice.
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
