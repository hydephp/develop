<h1 align=center>HydePHP - Source Monorepo</h1>
<h2 align=center>Contribute to the Core HydePHP Components in one place</h2>

<div align=center>

[![Test & Build](https://github.com/hydephp/develop/actions/workflows/continuous-integration.yml/badge.svg)](https://github.com/hydephp/develop/actions/workflows/continuous-integration.yml)
[![Framework Tests (Matrix)](https://github.com/hydephp/framework/actions/workflows/run-tests.yml/badge.svg)](https://github.com/hydephp/framework/actions/workflows/run-tests.yml)
[![Hyde Tests](https://github.com/hydephp/hyde/actions/workflows/run-tests.yml/badge.svg)](https://github.com/hydephp/hyde/actions/workflows/run-tests.yml)
</div>

<div align=center>

[![Test Coverage](https://codecov.io/gh/hydephp/develop/branch/master/graph/badge.svg?token=G6N2161TOT)](https://codecov.io/gh/hydephp/develop)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/hydephp/develop/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/hydephp/develop/?branch=master)
[![Psalm Type Coverage](https://shepherd.dev/github/hydephp/develop/coverage.svg)](https://shepherd.dev/github/hydephp/develop)

</div>

<div align=center>

![Latest Version on Packagist](https://img.shields.io/packagist/v/hyde/framework?include_prereleases)
![Total Downloads on Packagist](https://img.shields.io/packagist/dt/hyde/framework)
![License MIT](https://img.shields.io/github/license/hydephp/hyde)

</div>

> This repository holds the source code for the HydePHP core components. If you want to create a website with Hyde, check out the [HydePHP/Hyde repository](https://github.com/hydephp/hyde).

## Projects in this monorepo

[![Upcoming Documentation](https://img.shields.io/badge/-Upcoming%20Documentation-525252)](https://hydephp.github.io/develop/master/dev-docs/)
[![API Documentation](https://img.shields.io/badge/-API%20Documentation-525252)](https://hydephp.github.io/develop/master/api-docs/)
[![HTML Code Coverage](https://img.shields.io/badge/-HTML%20Code%20Coverage-525252)](https://hydephp.github.io/develop/master/coverage/)
[![This project is using Percy.io for visual regression testing.](https://percy.io/static/images/percy-badge.svg)](https://percy.io/9d5269d1/hydephp)

**HydePHP consists of a few core components, the development of which is done in this monorepo.**

The code pushed here is automatically split into separate read-only repositories for each component.
The two most important components are **Hyde** and **Framework**. We also use **HydeFront** for frontend assets.

**The Hyde package is what the end-user sees and interacts with.** When creating a new HydePHP site, this is done using the Hyde project. The package contains all the necessary files to run a HydePHP site and bootstraps the entire system.

**The Framework package holds most of the logic of the Hyde framework.** This is where all the data models, static site generators, HydeCLI commands, Blade views, and more, are stored. Having this in a package makes it much easier to version and update using Composer.

**The HydeFront package contains stylesheets and scripts** to help make HydePHP sites accessible and interactive. It also includes a pre-compiled TailwindCSS file containing all the styles needed for the built-in Blade templates.

### Quick reference overview

| **Package**           | **Monorepo path**                                        | **Readonly repository**                                                   | **Package location**                                                            |
|-----------------------|----------------------------------------------------------|---------------------------------------------------------------------------|---------------------------------------------------------------------------------|
| **Hyde**              | [Root directory](https://github.com/hydephp/develop)*    | [hydephp/hyde](https://github.com/hydephp/hyde)                           | [hyde/hyde](https://packagist.org/packages/hyde/hyde)                           |
| **Framework**         | [packages/framework](packages/framework)                 | [hydephp/framework](https://github.com/hydephp/framework)                 | [hyde/framework](https://packagist.org/packages/hyde/framework)                 |
| **Realtime Compiler** | [packages/realtime-compiler](packages/realtime-compiler) | [hydephp/realtime-compiler](https://github.com/hydephp/realtime-compiler) | [hyde/realtime-compiler](https://packagist.org/packages/hyde/realtime-compiler) |
| **Testing**           | [packages/testing](packages/testing)                     | [hydephp/testing](https://github.com/hydephp/testing)                     | [hyde/testing](https://packagist.org/packages/hyde/testing)                     |
| **UI Kit**            | [packages/ui-kit](packages/ui-kit)                       | [hydephp/ui-kit](https://github.com/hydephp/ui-kit)                       | [hyde/ui-kit](https://packagist.org/packages/hyde/ui-kit)                       |
| **HydeFront**         | [packages/hydefront](packages/hydefront)                 | [hydephp/hydefront](https://github.com/hydephp/hydefront)                 | [npm@hydefront](https://www.npmjs.com/package/hydefront)                        |


*The Hyde/Hyde project is stored in the monorepo root and works a bit differently from the others. Before pushing to the readonly repository, we apply persisted changes in the [`packages/hyde`](https://github.com/hydephp/develop/tree/master/packages/hyde) directory, then remove monorepo specific files.


### How the monorepo works

Changes to HydePHP including some first-party packages are made here. The changes are then pushed to the `develop` or `master` branches of the readonly repositories seen in the table above.


### Releases

The versioning between the Framework and Hyde packages are linked together Meaning that if Hyde get's a major release, so must Framework, and vice versa. To make this easier, we also publish major and minor releases in the monorepo. Patch releases are not published in the monorepo, and are instead handled by the individual packages.
