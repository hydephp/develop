# HydePHP - Monorepo Source Code
## Contribute to the Core HydePHP Components in one place

[![Test & Build](https://github.com/hydephp/develop/actions/workflows/continuous-integration.yml/badge.svg)](https://github.com/hydephp/develop/actions/workflows/continuous-integration.yml)
[![Framework Tests (Matrix)](https://github.com/hydephp/framework/actions/workflows/run-tests.yml/badge.svg)](https://github.com/hydephp/framework/actions/workflows/run-tests.yml)
[![Hyde Tests](https://github.com/hydephp/hyde/actions/workflows/run-tests.yml/badge.svg)](https://github.com/hydephp/hyde/actions/workflows/run-tests.yml)
[![codecov](https://codecov.io/gh/hydephp/develop/branch/master/graph/badge.svg?token=G6N2161TOT)](https://codecov.io/gh/hydephp/develop)

> This repository holds the source code for HydePHP. If you want to create a website with Hyde, checkout the [HydePHP/Hyde repository](https://github.com/hydephp/hyde).

## Projects in this monorepo

**HydePHP consists of a few core components, the development of which is done in this monorepo.**

The code pushed here is automatically split into separate read-only repositories for each component.
The two most important components are **Hyde** and **Framework**. We also use **HydeFront** for frontend assets.

**The Hyde package is what the end-user sees and interacts with.** When creating a new HydePHP site, this is done using the Hyde project. The package contains all the necessary files to run a HydePHP site and bootstraps the entire system.

**The Framework package holds most of the logic of the Hyde framework.** This is where all the data models, static site generators, HydeCLI commands, Blade views, and more, are stored. Having this in a package makes it much easier to version and update using Composer.

**The HydeFront package contains stylesheets and scripts** to help make HydePHP sites accessible and interactive. It also includes a pre-compiled TailwindCSS file containing all the styles needed for the built-in Blade templates.

### Quick reference overview

| **Package**           | **Monorepo path**                                        | **Mirror branch**                                                                              | **Readonly repository**                                                   | **Package location**                                                            |
|-----------------------|----------------------------------------------------------|------------------------------------------------------------------------------------------------|---------------------------------------------------------------------------|---------------------------------------------------------------------------------|
| **Hyde**              | [Root directory](https://github.com/hydephp/develop)     | [readonly-hyde-mirror](https://github.com/hydephp/develop/tree/readonly-hyde-mirror)           | [hydephp/hyde](https://github.com/hydephp/hyde)                           | [hyde/hyde](https://packagist.org/packages/hyde/hyde)                           |
| **Framework**         | [packages/framework](packages/framework)                 | [readonly-framework-mirror](https://github.com/hydephp/develop/tree/readonly-framework-mirror) | [hydephp/framework](https://github.com/hydephp/framework)                 | [hyde/framework](https://packagist.org/packages/hyde/framework)                 |
| **Realtime Compiler** | [packages/realtime-compiler](packages/realtime-compiler) | [readonly-rc-mirror](https://github.com/hydephp/develop/tree/readonly-rc-mirror)               | [hydephp/realtime-compiler](https://github.com/hydephp/realtime-compiler) | [hyde/realtime-compiler](https://packagist.org/packages/hyde/realtime-compiler) |
| **HydeFront**         | [packages/hydefront](packages/hydefront)                 | None yet (not fully integrated)                                                                | [hydephp/hydefront](https://github.com/hydephp/hydefront)                 | [npm@hydefront](https://www.npmjs.com/package/hydefront)                        |


## How the monorepo currently works

Changes to HydePHP including some first-party packages are made here.
Once pushed two important CI jobs kick in, one for testing and creating code reports and previews, and one for splitting the monorepo.

The monorepo is split in three* stages to decouple the process.

1. **Packaging** where we create action artifacts containing the package
2. **Splitting** where we upload the artifact to the corresponding readonly mirror branch.
3. **Pushing**   where we push the contents of the mirror branches to the develop branches of their repositories

*The Hyde/Hyde project is stored in the monorepo root and works a bit differently. Here don't package the data, instead we remove monorepo code and apply persisted data before running the next steps.

### Releases

The release cycle for Hyde/Hyde and Hyde/Framework are synced. Before creating the release we merge the packages' develop branches into their master branches.
Note that I'm just now testing out this release system, and it is entirely possible that we'll just keep minor versions in sync, and not bother with patches the framework changes much more frequently than hyde, leading to a bunch patch releases where there are no actual changes.

## Warning

This monorepo project is still new, and the internal structure of it may be changed without notice.
Changes pushed to the actual package repositories are only made when stable.
