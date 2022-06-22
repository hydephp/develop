<!-- NOTE FOR EDITORS: -->
<!-- Part of this file is machine edited, please leave the comment markers as they are. 
	 Also, please make sure to keep an empty line before and after each marker. -->

# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

<!-- UNRELEASED_START -->

## [Unreleased] - YYYY-MM-DD

### About

Keep an Unreleased section at the top to track upcoming changes.

This serves two purposes:

1. People can see what changes they might expect in upcoming releases
2. At release time, you can move the Unreleased section changes into a new release version section.

### Added
- Added back the AppServiceProvider
- Added system for defining easy to use post-build hooks https://github.com/hydephp/develop/issues/79
- Added configuration option to exclude documentation pages from showing up in the JSON search index


### Changed
- Changelog files in the documentation source directory are now ignored by the JSON search index by default

### Deprecated
- Deprecate the site_output_path option in the Hyde config file. Will be handled by the HydeServiceProvider.

### Removed
- Removed the deprecated bootstrap directory

### Fixed
- for any bug fixes.

### Security
- Bump guzzlehttp/guzzle from 7.4.4 to 7.4.5

<!-- UNRELEASED_END -->

---

### Changelog

<!-- CHANGELOG_START -->

## v0.39.0-beta - 2022-06-20

### Added
- Added a helper to all page models to get an array of all its source files https://github.com/hydephp/develop/issues/44
- Added a helper to all page models to parse source files directly into an object https://github.com/hydephp/develop/issues/40
- Adds the MarkdownDocumentContract interface to markdown based pages to keep a consistent and predictable state
- Adds .gitkeep files to persist empty directories
- internal: Add more tests
- internal: Add packages/hyde/composer.json for persisted data instead of removed update script

### Changed
- Changed welcome page title https://github.com/hydephp/develop/issues/52
- Add `rel="nofollow"` to the image author links https://github.com/hydephp/develop/issues/19
- Changed the default position of the automatic navigation menu link to the right, also making it configurable
- Renamed deprecated Hyde::docsDirectory() helper to suggested Hyde::getDocumentationOutputDirectory()
- Makes the constructor arguments for Markdown page models optional https://github.com/hydephp/develop/issues/65
- Added the Hyde/Framework composer.lock to .gitignore as we keep a master lock file in the monorepo
- Changed namespace for Hyde/Framework tests from `Hyde\Testing\Framework` to `Hyde\Framework\Testing`
- Directories are created when needed, instead of each time the service provider boots up
- internal: Add back codecov.io to pull request tests https://github.com/hydephp/develop/issues/37
- internal: Refactor test that interact with the filesystem to be more granular
- internal: Update Monorepo structure to move persisted data for the Hyde package into the packages directory

### Removed
- Removed the Hyde::getLatestPosts() helper which was deprecated in v0.34.x and was replaced with MarkdownPost::getLatestPosts()
- Removes the long deprecated CreatesDefaultDirectories class
- internal: Removed composer update script

### Fixed
- Add changelog to export-ignore, https://github.com/hydephp/framework/issues/537


## v0.38.0-beta - 2022-06-18

### About

This release refactors the test suite, compartmentalizing test code into the respective package directories. 
This does not affect the behavior of the library, but it does affect how package developers run the test suites.

### Added
- internal: Adds high level tests for the Hyde package.
- internal: Add GitHub test workflows for Hyde/Hyde and Hyde/Framework

### Changed
- Formats code to the PSR-2 standard.

- internal: Move Framework tests from the monorepo into the Framework package.
- internal: Rename monorepo workflow `build-test.yml` to `continuous-integration.yml`.
- internal: Change testing namespaces update `phpunit.xml.dist` correspondingly.
- internal: Add static analysis tests to the continuous integration workflow.
- internal: Add matrix test runners to the continuous integration workflow.


## v0.37.2-beta - 2022-06-17

### About

This release brings internal restructuring to the Hyde monorepo,
adding a helper command to manage the new release cycle.

### Added
- Add internal `monorepo:release` command 

### Changed
- Changed to keep only a single `CHANGELOG.md` file for Hyde/Hyde and Hyde/Framework


## v0.37.1-beta - 2022-06-16 - Update validation test

### About

If there are no documentation pages there is no need for an index page, and the test can safely be skipped.

### What's Changed
* v0.37.0-beta - Create custom validator test framework by @caendesilva in https://github.com/hydephp/develop/pull/45
* Skip documentation index validation test if the _docs directory is empty by @caendesilva in https://github.com/hydephp/develop/pull/48


**Full Changelog**: https://github.com/hydephp/develop/compare/v0.36.0-beta...v0.37.1-beta


## v0.37.0-beta - 2022-06-16 - Replace dependency with custom validator implementation

### What's Changed
* v0.37.0-beta - Create custom validator test framework by @caendesilva in https://github.com/hydephp/develop/pull/45


**Full Changelog**: https://github.com/hydephp/develop/compare/v0.36.0-beta...v0.37.0-beta.1


## v0.36.0-beta - 2022-06-16 - Add package auto-discovery

### What's Changed
* Improve transformation of the hyde/hyde composer.json in the monorepo split job by @caendesilva in https://github.com/hydephp/develop/pull/33
* v0.36.x - Add package auto-discovery by @caendesilva in https://github.com/hydephp/develop/pull/35


**Full Changelog**: https://github.com/hydephp/develop/compare/v0.35.0-beta.1...v0.36.0-beta


## v0.35.0-beta - 2022-06-14 - Initial Monorepo Release

### What's Changed

* Restore master project by @caendesilva in https://github.com/hydephp/develop/pull/1
* Merge Hyde/Framework into packages/framework by @caendesilva in https://github.com/hydephp/develop/pull/2
* Refactor test suite, moving tests into Hyde root and updating some of them by @caendesilva in https://github.com/hydephp/develop/pull/3
* Remove default AppServiceProvider.php, fix #5 by @caendesilva in https://github.com/hydephp/develop/pull/6
* Fix #7: Remove unrelated configuration files from the framework package by @caendesilva in https://github.com/hydephp/develop/pull/8
* Refactor bootstrapping process by @caendesilva in https://github.com/hydephp/develop/pull/9
* Remove layover framework test files by @caendesilva in https://github.com/hydephp/develop/pull/10
* Import hydefront package by @caendesilva in https://github.com/hydephp/develop/pull/11
* Import hydephp/realtime-compiler to packages/ by @caendesilva in https://github.com/hydephp/develop/pull/16
* Handle moving of the bootstrap file to provide backwards compatibility for the migration period by @caendesilva in https://github.com/hydephp/develop/pull/17
* Import hydephp/docs by @caendesilva in https://github.com/hydephp/develop/pull/18
* Create readonly mirrors by @caendesilva in https://github.com/hydephp/develop/pull/21
* Add Rocket dashboard subrepository by @caendesilva in https://github.com/hydephp/develop/pull/25
* Work in progress single-file dashboard for the HydeRC by @caendesilva in https://github.com/hydephp/develop/pull/26
* Create dashboard template by @caendesilva in https://github.com/hydephp/develop/pull/27


**Full Changelog**: https://github.com/hydephp/develop/commits/v0.35.0-beta


<!-- CHANGELOG_END -->

---

## Archive (pre v0.35.0)

In v0.35.0 the Hyde project source was moved into the [HydePHP/Develop monorepo](https://github.com/hydephp/develop) where the changelog is now handled. Releases in Hyde/Hyde and Hyde/Framework are synced one-to-one since this change.

- [Hyde/Hyde Archive (pre v0.35.0)](#hydehyde-archive-pre-v0350)
- [Hyde/Framework Archive (pre v0.35.0)](#hydeframework-archive-pre-v0350)

### Hyde/Hyde Archive (pre v0.35.0)



All notable changes to this project will be documented in this file. Dates are displayed in UTC.

Generated by [`auto-changelog`](https://github.com/CookPete/auto-changelog).

#### [v0.34.1-beta](https://github.com/hydephp/hyde/compare/v0.34.0-beta...v0.34.1-beta)

> 11 June 2022

- Bump guzzlehttp/guzzle from 7.4.3 to 7.4.4 [`#187`](https://github.com/hydephp/hyde/pull/187)

#### [v0.34.0-beta](https://github.com/hydephp/hyde/compare/v0.33.0-beta...v0.34.0-beta)

> 6 June 2022

- Update Framework to v0.34.x [`c5c4f05`](https://github.com/hydephp/hyde/commit/c5c4f05fb65306768df1b7db53e41c19c0a34915)
- Update README.md [`6cbcf8b`](https://github.com/hydephp/hyde/commit/6cbcf8b4eb99dde9ac09d94125ccb71c857b5ddd)

#### [v0.33.0-beta](https://github.com/hydephp/hyde/compare/v0.32.3-beta...v0.33.0-beta)

> 4 June 2022


#### [v0.32.3-beta](https://github.com/hydephp/hyde/compare/v0.32.2-beta...v0.32.3-beta)

> 4 June 2022

- Move back hyde/realtime-compiler to hyde/hyde [`#184`](https://github.com/hydephp/hyde/pull/184)
- Update composer.lock [`b36937d`](https://github.com/hydephp/hyde/commit/b36937d5a2c354fd102d5bb4244bb1e167f0dde7)

#### [v0.32.2-beta](https://github.com/hydephp/hyde/compare/v0.32.1-beta...v0.32.2-beta)

> 4 June 2022

- Persist file cache data directory [`347e393`](https://github.com/hydephp/hyde/commit/347e3936f4914c73eb10c9ecf637521dc43d3308)
- Create cache .gitignore [`14e57b6`](https://github.com/hydephp/hyde/commit/14e57b6846e551cf138afc2313254a2c226ecc5f)

#### [v0.32.1-beta](https://github.com/hydephp/hyde/compare/v0.32.0-beta...v0.32.1-beta)

> 4 June 2022

- Update frontend and framework files [`#180`](https://github.com/hydephp/hyde/pull/180)
- Update composer.lock [`62b9b4e`](https://github.com/hydephp/hyde/commit/62b9b4e697fa0687a24f3a50e0700419f10b9b01)
- Automatic build update [`626983a`](https://github.com/hydephp/hyde/commit/626983ae06299d467acdeac07564db603ae017fc)

#### [v0.32.0-beta](https://github.com/hydephp/hyde/compare/v0.31.0-beta...v0.32.0-beta)

> 4 June 2022

- Update composer.lock [`e26171a`](https://github.com/hydephp/hyde/commit/e26171a3f9bda19a589c59f8d06b1432d100459c)
- Remove composer requirements handled by Framework [`b04754b`](https://github.com/hydephp/hyde/commit/b04754b9021011e8e7ae13deb83b3cee97bfc0b2)
- Update Hyde/Framework to v0.32.x [`4cd1161`](https://github.com/hydephp/hyde/commit/4cd11619225acf73971960b7a3caca74abdfc08b)

#### [v0.31.0-beta](https://github.com/hydephp/hyde/compare/v0.30.1-beta...v0.31.0-beta)

> 4 June 2022

- Update frontend and framework files [`#177`](https://github.com/hydephp/hyde/pull/177)
- Update to Framework 0.31.x [`2da64b4`](https://github.com/hydephp/hyde/commit/2da64b47738f08fc4119db43fc90a2c848f2f162)
- Automatic build update [`9a59cb6`](https://github.com/hydephp/hyde/commit/9a59cb60d8f80fc51bd9fe31e86902c4e63667d6)

#### [v0.30.1-beta](https://github.com/hydephp/hyde/compare/v0.30.0-beta...v0.30.1-beta)

> 31 May 2022

- Fix package.json version formatting error [`#175`](https://github.com/hydephp/hyde/pull/175)

#### [v0.30.0-beta](https://github.com/hydephp/hyde/compare/v0.29.0-beta...v0.30.0-beta)

> 31 May 2022

- Update Hyde to v0.30.x [`80d91b5`](https://github.com/hydephp/hyde/commit/80d91b5e050de7ff7eb9e97508f5ccef97d91525)

#### [v0.29.0-beta](https://github.com/hydephp/hyde/compare/v0.28.1-beta...v0.29.0-beta)

> 30 May 2022

- Update frontend and framework files [`#172`](https://github.com/hydephp/hyde/pull/172)
- Fix #169: remove white-space: pre from &lt;code&gt;, allowing it to wrap [`#170`](https://github.com/hydephp/hyde/pull/170)
- Merge pull request #170 from hydephp/Update-tailwind-config-to-allow-code-tags-to-wrap [`#169`](https://github.com/hydephp/hyde/issues/169)
- Fix #169: remove white-space: pre from &lt;code&gt; [`#169`](https://github.com/hydephp/hyde/issues/169)
- Update lock file [`f037c4d`](https://github.com/hydephp/hyde/commit/f037c4d3fd6d677d742ae57c18c4a712e59e6cef)
- Update hyde/realtime-compiler to v1.3.0 [`91e822a`](https://github.com/hydephp/hyde/commit/91e822a29efeb02b6d1d481c0123a8130e6b6685)
- Update to Framework v0.29.0-beta [`9a624de`](https://github.com/hydephp/hyde/commit/9a624de76b573dc5fc9911a4aae907cf9a5f2b1e)

#### [v0.28.1-beta](https://github.com/hydephp/hyde/compare/v0.28.0-beta...v0.28.1-beta)

> 29 May 2022

- Bump guzzlehttp/guzzle from 7.4.2 to 7.4.3 [`#167`](https://github.com/hydephp/hyde/pull/167)

#### [v0.28.0-beta](https://github.com/hydephp/hyde/compare/v0.27.1-beta...v0.28.0-beta)

> 23 May 2022

- Update Hyde to v0.28.x [`16da0b3`](https://github.com/hydephp/hyde/commit/16da0b3fc6791f6c8abd1b31d213a47e9d7bb5cb)
- Create FUNDING.yml [`1272144`](https://github.com/hydephp/hyde/commit/1272144340f0f123db9a3b9e002383409a255050)

#### [v0.27.1-beta](https://github.com/hydephp/hyde/compare/v0.27.0-beta...v0.27.1-beta)

> 21 May 2022

- Upgrade hyde/framework v0.27.5-beta =&gt; v0.27.11-beta [`809c700`](https://github.com/hydephp/hyde/commit/809c7000595af120fa39971d9c5f6770d046c4a5)

#### [v0.27.0-beta](https://github.com/hydephp/hyde/compare/v0.26.0-beta...v0.27.0-beta)

> 19 May 2022

- Update to v0.27.x [`ecc8fd1`](https://github.com/hydephp/hyde/commit/ecc8fd1cf052d4f2ac8ae798c2a8a778a46d605d)

#### [v0.26.0-beta](https://github.com/hydephp/hyde/compare/v0.25.0-beta...v0.26.0-beta)

> 18 May 2022

- Update to v0.26.x [`123bdeb`](https://github.com/hydephp/hyde/commit/123bdebd7d08541507a1ef3f92c5c0ea115f2edc)
- Breaking: Update config to v0.26.x-dev-master [`268b2a6`](https://github.com/hydephp/hyde/commit/268b2a6ed8be4a8f9efa7f977ae9ba25fd963bc3)
- Update framework to dev-master [`ddc37cf`](https://github.com/hydephp/hyde/commit/ddc37cf6d3e1ade1a3a121b165dc75b45e743d72)

#### [v0.25.0-beta](https://github.com/hydephp/hyde/compare/v0.24.1-beta...v0.25.0-beta)

> 17 May 2022

- Update frontend and framework files [`#161`](https://github.com/hydephp/hyde/pull/161)
- Update frontend and framework files [`#159`](https://github.com/hydephp/hyde/pull/159)
- Update to v0.25.x [`497d540`](https://github.com/hydephp/hyde/commit/497d540953c95adc8ec08df9424ff33328477d91)
- Automatic build update [`64c2bd6`](https://github.com/hydephp/hyde/commit/64c2bd6b106f19d0255c0ed2bf599e555a2cdd69)
- Automatic build update [`915cab8`](https://github.com/hydephp/hyde/commit/915cab8802758a7fbb2d31df7d8bfba3d1a781d2)

#### [v0.24.1-beta](https://github.com/hydephp/hyde/compare/v0.24.0-beta...v0.24.1-beta)

> 11 May 2022

- Update frontend and framework files [`#157`](https://github.com/hydephp/hyde/pull/157)
- Disable cache [`8a48e3e`](https://github.com/hydephp/hyde/commit/8a48e3e54bdda7b0568b189fd609bacb05d1298e)
- Update SECURITY.md [`958d92e`](https://github.com/hydephp/hyde/commit/958d92e4782a37b9f655e0fee50ad3941354fce2)
- Bump Hyde/Framework [`cfed837`](https://github.com/hydephp/hyde/commit/cfed837ad3b16b2691d83b4c8af70697df317e9b)

#### [v0.24.0-beta](https://github.com/hydephp/hyde/compare/v0.23.0-beta...v0.24.0-beta)

> 11 May 2022

- Update dependencies for release [`575338d`](https://github.com/hydephp/hyde/commit/575338d6caaafeb60aa14e94286726a1d590ddb5)

#### [v0.23.0-beta](https://github.com/hydephp/hyde/compare/v0.22.0-beta...v0.23.0-beta)

> 6 May 2022

- Update frontend and framework files [`#152`](https://github.com/hydephp/hyde/pull/152)
- Run cache after installing [`25f8581`](https://github.com/hydephp/hyde/commit/25f8581739417ff4e3f642cebdeb2ffca5cd5924)
- Update hyde/framework [`cc66395`](https://github.com/hydephp/hyde/commit/cc6639558289c3cd5c4d6307fbca380016b6ae57)

#### [v0.22.0-beta](https://github.com/hydephp/hyde/compare/v0.21.0-beta...v0.22.0-beta)

> 4 May 2022

- Update frontend and framework files [`#149`](https://github.com/hydephp/hyde/pull/149)
- Fix #146 by adding _pages to Tailwind content [`#148`](https://github.com/hydephp/hyde/pull/148)
- Add back _site to Tailwind content array [`#147`](https://github.com/hydephp/hyde/pull/147)
- Update frontend and framework files [`#143`](https://github.com/hydephp/hyde/pull/143)
- Merge pull request #148 from hydephp/caendesilva-patch-1 [`#146`](https://github.com/hydephp/hyde/issues/146)
- Fix #146 by adding _pages to Tailwind content [`#146`](https://github.com/hydephp/hyde/issues/146)
- Automatic build update [`5f656d0`](https://github.com/hydephp/hyde/commit/5f656d04ea94fc8d9fa63b99d681c561a503d1aa)
- Remove reliance on deprecated service [`71bb359`](https://github.com/hydephp/hyde/commit/71bb359fa959b17f175adff406dd86b9bfa6dfd5)

#### [v0.21.0-beta](https://github.com/hydephp/hyde/compare/v0.20.0-beta...v0.21.0-beta)

> 3 May 2022


#### [v0.20.0-beta](https://github.com/hydephp/hyde/compare/v0.19.0-beta...v0.20.0-beta)

> 3 May 2022

- Update max-width for blog posts [`#139`](https://github.com/hydephp/hyde/pull/139)
- Update config to v0.20.x [`87c6748`](https://github.com/hydephp/hyde/commit/87c6748825247bcc558fdbf1dbdad6cac70748b3)
- Rename workflow and jobs [`c67f2c0`](https://github.com/hydephp/hyde/commit/c67f2c08a25d28a7ec8520a2ccb79e3fa2227c11)

#### [v0.19.0-beta](https://github.com/hydephp/hyde/compare/v0.18.0-beta...v0.19.0-beta)

> 1 May 2022

- Update frontend assets [`#136`](https://github.com/hydephp/hyde/pull/136)
- Update frontend assets [`#134`](https://github.com/hydephp/hyde/pull/134)
- Add Laravel Mix #124 [`#129`](https://github.com/hydephp/hyde/pull/129)
- Fix #127 [`#127`](https://github.com/hydephp/hyde/issues/127)
- Clone repo directly to fix #133 [`#133`](https://github.com/hydephp/hyde/issues/133)
- Fix #131 [`#131`](https://github.com/hydephp/hyde/issues/131)
- Add Laravel Mix [`dc62438`](https://github.com/hydephp/hyde/commit/dc624383bdb4ca8d3e88209dc10c66d2c9e082a1)
- Remove laminas/laminas-text [`fa23c60`](https://github.com/hydephp/hyde/commit/fa23c60cfaa42805dfc6a04efb3794620d0239dc)
- Add PostCSS [`db399c4`](https://github.com/hydephp/hyde/commit/db399c439b0fe8f07ba45566a5d8825545160de7)

#### [v0.18.0-beta](https://github.com/hydephp/hyde/compare/v0.17.1-beta...v0.18.0-beta)

> 29 April 2022

- Fix https://github.com/hydephp/hydefront/issues/1 [`#1`](https://github.com/hydephp/hydefront/issues/1)
- Fix https://github.com/hydephp/hyde/issues/120 [`#120`](https://github.com/hydephp/hyde/issues/120)
- Reset the application only once [`8d2dec7`](https://github.com/hydephp/hyde/commit/8d2dec786d52117e778d6d2ed38595f3a1a887e4)
- Create build-and-update-hydefront.yml [`eca910c`](https://github.com/hydephp/hyde/commit/eca910c7bd45b47b4f4d8a369468c810533fba35)
- Implement https://github.com/hydephp/framework/issues/182 [`f0b6da0`](https://github.com/hydephp/hyde/commit/f0b6da09caa8e906dba549792cc6dfb48fdeee41)

#### [v0.17.1-beta](https://github.com/hydephp/hyde/compare/v0.17.0-beta...v0.17.1-beta)

> 28 April 2022

- Remove compiled files and fix wrong homepage layout [`f92e4b7`](https://github.com/hydephp/hyde/commit/f92e4b7afee871dd69570a6a1f5915a6bc26a041)

#### [v0.17.0-beta](https://github.com/hydephp/hyde/compare/v0.16.1-beta...v0.17.0-beta)

> 28 April 2022

- Remove GitHub test workflows from Hyde/Hyde, moving them into Hyde/Framework  [`#116`](https://github.com/hydephp/hyde/pull/116)
- Move Framework tests to the Hyde/Framework package [`#115`](https://github.com/hydephp/hyde/pull/115)
- Resolve https://github.com/hydephp/framework/issues/186 [`#186`](https://github.com/hydephp/framework/issues/186)
- Move tests to Framework [`eba459c`](https://github.com/hydephp/hyde/commit/eba459c6ea9ce717ae1d7490eeb73b8523c0834a)
- Remove deprecated trait [`87f1659`](https://github.com/hydephp/hyde/commit/87f1659179be5254d6650fed5bd1ded6ce312df5)
- Remove deprecated Setup directory [`f5a9be5`](https://github.com/hydephp/hyde/commit/f5a9be5218b7dc1378928d12bd7fafc80a377b1a)

#### [v0.16.1-beta](https://github.com/hydephp/hyde/compare/v0.16.0-beta...v0.16.1-beta)

> 28 April 2022

- Delete codeql as the JS has moved to HydeFront [`ef7e94e`](https://github.com/hydephp/hyde/commit/ef7e94e07c870e672ef39811addf87964c21df1c)
- Change test coverage to code reports [`b1fd3e9`](https://github.com/hydephp/hyde/commit/b1fd3e94221d1468d932a5eecee729662729fe34)
- Add more reporting outputs [`a4ac8e6`](https://github.com/hydephp/hyde/commit/a4ac8e696e31f7b4f4fd81c1be042853495267d8)

#### [v0.16.0-beta](https://github.com/hydephp/hyde/compare/v0.15.0-beta...v0.16.0-beta)

> 27 April 2022

- Update namespace [`50695fc`](https://github.com/hydephp/hyde/commit/50695fc097f3bab0d4380e6a07e2a87f7b937675)
- 0.15.x Update namespace [`8c084b9`](https://github.com/hydephp/hyde/commit/8c084b9e866f814d16e8085be65d9e0cb28d9663)

#### [v0.15.0-beta](https://github.com/hydephp/hyde/compare/v0.14.0-beta...v0.15.0-beta)

> 27 April 2022

- Remove files moved to CDN [`521d790`](https://github.com/hydephp/hyde/commit/521d79005537edb65ed5e740091385bd1a9d96c3)
- Update tests for removed frontend assets [`fa68c37`](https://github.com/hydephp/hyde/commit/fa68c3718b1454663089e3a53791b6996f5d1a1d)

#### [v0.14.0-beta](https://github.com/hydephp/hyde/compare/v0.13.0-beta...v0.14.0-beta)

> 21 April 2022

- Change update:resources command signature to update:assets [`#100`](https://github.com/hydephp/hyde/pull/100)
- Rename directory resources/frontend to resources/assets [`#99`](https://github.com/hydephp/hyde/pull/99)
- Fix https://github.com/hydephp/framework/issues/156 [`#156`](https://github.com/hydephp/framework/issues/156)
- Publish the assets [`330971d`](https://github.com/hydephp/hyde/commit/330971d30bc77dc8ff89c4b724af6bd6f9600eb3)
- Add the Markdown features tests [`71c0936`](https://github.com/hydephp/hyde/commit/71c093657af227ea7c58167a6ceaca9b291ecf38)
- Update composer dependencies [`8a1ebe4`](https://github.com/hydephp/hyde/commit/8a1ebe4480f9bc6fea553d8136c5c68fcec18b95)

#### [v0.13.0-beta](https://github.com/hydephp/hyde/compare/v0.12.0-beta...v0.13.0-beta)

> 20 April 2022

- Remove BrowserSync and other dependencies [`#93`](https://github.com/hydephp/hyde/pull/93)
- Create the tests [`f30c375`](https://github.com/hydephp/hyde/commit/f30c375c9c1c88182bff58d31ac430ce0029f35b)
- Republish the config [`c290249`](https://github.com/hydephp/hyde/commit/c290249cd52a0cc07211290bb49b4223a4687217)
- Update tests for 0.13.x [`f6de746`](https://github.com/hydephp/hyde/commit/f6de7469594dc3d40a94fa461a201371b0124e63)

#### [v0.12.0-beta](https://github.com/hydephp/hyde/compare/v0.11.0-beta...v0.12.0-beta)

> 19 April 2022

- Clean up Readme [`5eb9e5e`](https://github.com/hydephp/hyde/commit/5eb9e5ee114a55a76984fdd163f683770b53afad)
- Create the test [`7a02299`](https://github.com/hydephp/hyde/commit/7a02299b41ba45ac1b7fc25e0081ae891c136ca2)
- Add Features section [`85e8a4f`](https://github.com/hydephp/hyde/commit/85e8a4fd8275f8cf5b979dfed118d55164ecea92)

#### [v0.11.0-beta](https://github.com/hydephp/hyde/compare/v0.10.0-beta...v0.11.0-beta)

> 17 April 2022

- Add the realtime compiler extension [`90989c1`](https://github.com/hydephp/hyde/commit/90989c13a7cf87ef637a58e545acee16d8607716)
- Streamline Readme [`5860c04`](https://github.com/hydephp/hyde/commit/5860c0447e85571b5ead852ac1dfc5e3ff88fd80)

#### [v0.10.0-beta](https://github.com/hydephp/hyde/compare/v0.9.0-alpha...v0.10.0-beta)

> 12 April 2022

- Add darkmode support [`#86`](https://github.com/hydephp/hyde/pull/86)
- Remove the deprecated and unused service provider [`#85`](https://github.com/hydephp/hyde/pull/85)
- Updates the frontend and adds the tests for https://github.com/hydephp/framework/pull/102 [`#84`](https://github.com/hydephp/hyde/pull/84)
- Refactor tests [`#82`](https://github.com/hydephp/hyde/pull/82)
- Clean up repo [`#79`](https://github.com/hydephp/hyde/pull/79)
- Change blade source directory [`#75`](https://github.com/hydephp/hyde/pull/75)
- Bump composer packages [`#72`](https://github.com/hydephp/hyde/pull/72)
- Companion branch to https://github.com/hydephp/framework/pull/84 [`#71`](https://github.com/hydephp/hyde/pull/71)
- Internal build service refactor tie in [`#65`](https://github.com/hydephp/hyde/pull/65)
- Remove versioning from matrix, fix https://github.com/hydephp/framework/issues/93 [`#93`](https://github.com/hydephp/framework/issues/93)
- Publish the compiled assets [`7a24814`](https://github.com/hydephp/hyde/commit/7a248146f4ce3ba2296171af26760a141cbd912f)
- Update tests for Build Service refactor [`06c8048`](https://github.com/hydephp/hyde/commit/06c80480a6a5fc2221e4fd3d457c06a3748cbedb)
- Update the test [`874c2a4`](https://github.com/hydephp/hyde/commit/874c2a41aebaab4510e70e2430dfb283607129b1)

#### [v0.9.0-alpha](https://github.com/hydephp/hyde/compare/v0.8.0-alpha...v0.9.0-alpha)

> 7 April 2022

- Change where and how stylesheets and scripts are stored and handled [`#63`](https://github.com/hydephp/hyde/pull/63)
- Move the resource files [`fb3b660`](https://github.com/hydephp/hyde/commit/fb3b660bd5377d60658bdd83ac38a6bbab80fe0e)
- Add the test [`51d99b2`](https://github.com/hydephp/hyde/commit/51d99b2e0d752266d4cf6161cb6c5b57ada153de)
- Publish the resources [`bf3b20d`](https://github.com/hydephp/hyde/commit/bf3b20d99d62b913d8d96bd6250e47de8e2a126a)

#### [v0.8.0-alpha](https://github.com/hydephp/hyde/compare/v0.7.3-alpha...v0.8.0-alpha)

> 3 April 2022

- Clean up test code and fix mismatched test namespace [`#59`](https://github.com/hydephp/hyde/pull/59)
- Update the navigation menu frontend [`#58`](https://github.com/hydephp/hyde/pull/58)
- Add Changelog.md [`9bff522`](https://github.com/hydephp/hyde/commit/9bff522faa4970d5742d3238f11f0d3b0335fa77)
- Create CODE_OF_CONDUCT.md [`ffde383`](https://github.com/hydephp/hyde/commit/ffde383cdd51d9e8a691f3d17c7d09fb5d174a33)
- Create a test runner with a backup feature [`605ed46`](https://github.com/hydephp/hyde/commit/605ed463809e7da716512709017f8a62b8d93167)

#### [v0.7.3-alpha](https://github.com/hydephp/hyde/compare/v0.7.2-alpha...v0.7.3-alpha)

> 1 April 2022

- Fix outdated welcome page links [`323ea17`](https://github.com/hydephp/hyde/commit/323ea176294f517a335e0c8ee7e1c1af0b46981d)

#### [v0.7.2-alpha](https://github.com/hydephp/hyde/compare/v0.7.1-alpha...v0.7.2-alpha)

> 1 April 2022

- Create the test [`0a8a00e`](https://github.com/hydephp/hyde/commit/0a8a00e9b6516f655495dc0fd1365a92283917ef)
- Create the test [`f445bd5`](https://github.com/hydephp/hyde/commit/f445bd57aff02d1619320ecd5dcbea9a09112c68)
- Implement the --force option [`2fe366c`](https://github.com/hydephp/hyde/commit/2fe366cfc7ee418eeb4305cfa4dfecac0053a0ab)

#### [v0.7.1-alpha](https://github.com/hydephp/hyde/compare/v0.7.0-alpha...v0.7.1-alpha)

> 1 April 2022

- Add SASS as a dev dependency [`#55`](https://github.com/hydephp/hyde/pull/55)

#### [v0.7.0-alpha](https://github.com/hydephp/hyde/compare/v0.6.0-alpha...v0.7.0-alpha)

> 1 April 2022

- Remove _authors and _drafts directories #48 [`#53`](https://github.com/hydephp/hyde/pull/53)
- Create the first two tests [`fdd197c`](https://github.com/hydephp/hyde/commit/fdd197c0e8ef6657f289ac3a3c20dbc654a6c9f8)
- Create the test [`6c43c41`](https://github.com/hydephp/hyde/commit/6c43c414a2bb029e61160067c5f479d357271c98)
- Update author yml config path [`67af952`](https://github.com/hydephp/hyde/commit/67af952e2d5f5c909e68ec8138a601c8349fe61c)

#### [v0.6.0-alpha](https://github.com/hydephp/hyde/compare/v0.5.0-alpha...v0.6.0-alpha)

> 30 March 2022

- Move scripts into app.js [`#51`](https://github.com/hydephp/hyde/pull/51)
- Update command class names [`#49`](https://github.com/hydephp/hyde/pull/49)
- Update to latest Framework version [`24d666d`](https://github.com/hydephp/hyde/commit/24d666d4d326dfcab92e7a21aca7c0d0e551897a)
- Add the test [`3554c33`](https://github.com/hydephp/hyde/commit/3554c332756e7b44dad0921f45cb00075519125f)
- 0.6.0 Add the test [`0e99c56`](https://github.com/hydephp/hyde/commit/0e99c569e7fe8bb866890e7869bf5de74a987eab)

#### [v0.5.0-alpha](https://github.com/hydephp/hyde/compare/v0.4.1-alpha...v0.5.0-alpha)

> 25 March 2022

- Remove legacy test [`7cee208`](https://github.com/hydephp/hyde/commit/7cee2080f57a3d8bc9cd4a6479ab71486dfdabf6)
- Add tests for installer [`dfed2d2`](https://github.com/hydephp/hyde/commit/dfed2d2ee55bf51694f138f8aa1e7ef2794c7fbf)
- #37 Add more tests: DebugCommand [`ab1758f`](https://github.com/hydephp/hyde/commit/ab1758fb5781b92e6814382b2e0613ad8ab27fd7)

#### [v0.4.1-alpha](https://github.com/hydephp/hyde/compare/v0.4.0-alpha...v0.4.1-alpha)

> 25 March 2022

- Bump minimist from 1.2.5 to 1.2.6 [`#47`](https://github.com/hydephp/hyde/pull/47)
- #37 Add more tests: HydeServiceProvider [`ae8673f`](https://github.com/hydephp/hyde/commit/ae8673f6ae4afde43395e52ea2023a48b3bf73b4)
- Inline the stream variable to fix missing file error [`bca234c`](https://github.com/hydephp/hyde/commit/bca234c40ba35f3d90c4a4cb756d75070938ec9d)
- Update to Framework 0.5.1 [`449e051`](https://github.com/hydephp/hyde/commit/449e0511e43b9b735ca4cc0cea6e3da037c8c572)

#### [v0.4.0-alpha](https://github.com/hydephp/hyde/compare/v0.3.3-alpha...v0.4.0-alpha)

> 24 March 2022

- 0.4.0 Update which adds several new tests tying into framework v0.5.0 [`#46`](https://github.com/hydephp/hyde/pull/46)
- Format tests to PSR2 [`e08aba6`](https://github.com/hydephp/hyde/commit/e08aba6ef4d7214990dc5f8f3869761e61ee553f)
- Create test for publish homepage command [`4e0f828`](https://github.com/hydephp/hyde/commit/4e0f828bc7284c38c10e46ed1e47a48b7c316c60)
- Update framework version to tie into new release [`e4944c8`](https://github.com/hydephp/hyde/commit/e4944c8442a992b26585d8933fe0d01282a5ee7e)

#### [v0.3.3-alpha](https://github.com/hydephp/hyde/compare/v0.3.2-alpha...v0.3.3-alpha)

> 23 March 2022

- Unlock framework version to patch error in last release [`4423513`](https://github.com/hydephp/hyde/commit/4423513ba82672c4bf19042330e8d684559209bb)
- Update config [`a480b0a`](https://github.com/hydephp/hyde/commit/a480b0a876b260d7a6e271713ad5489fb99581e2)

#### [v0.3.2-alpha](https://github.com/hydephp/hyde/compare/v0.3.1-alpha...v0.3.2-alpha)

> 23 March 2022

- Increase link contrast to fix accessibility issue [`#45`](https://github.com/hydephp/hyde/pull/45)
- Add the Site URL setting [`05211b9`](https://github.com/hydephp/hyde/commit/05211b9c55859588ba402b48c38c5d5e2fdd21a5)
- Update config [`8c0d331`](https://github.com/hydephp/hyde/commit/8c0d33158941579cf309847e865c8a0dda3772ad)
- Remove dev files from gitignore [`f52d471`](https://github.com/hydephp/hyde/commit/f52d4718b7764e4e379cd931bbd195be4369ffba)

#### [v0.3.1-alpha](https://github.com/hydephp/hyde/compare/v0.3.0-alpha...v0.3.1-alpha)

> 23 March 2022

- Replace the default empty blog listing index page with a new welcome screen [`#44`](https://github.com/hydephp/hyde/pull/44)
- Replace the default page [`d747290`](https://github.com/hydephp/hyde/commit/d74729050fa36d988b36a978cedaba1bf3e77a4f)
- Add the links [`b8cd49c`](https://github.com/hydephp/hyde/commit/b8cd49c2927c70b0c11e6d990c47adebdcdba8e4)
- Add info about the new build --clean option [`efca81f`](https://github.com/hydephp/hyde/commit/efca81f9d222a8409bdd079f67d1048e48c9d30a)

#### [v0.3.0-alpha](https://github.com/hydephp/hyde/compare/v0.2.1-alpha...v0.3.0-alpha)

> 22 March 2022

- v0.3 - Hyde Core Separation - Contains breaking changes [`#36`](https://github.com/hydephp/hyde/pull/36)
- Hyde Core Separation - Contains breaking changes [`#35`](https://github.com/hydephp/hyde/pull/35)
- Allow the view source directory to be modified at runtime [`#34`](https://github.com/hydephp/hyde/pull/34)
- Add a path helper to unify path referencing [`#33`](https://github.com/hydephp/hyde/pull/33)
- Successfully moved Core into temporary package [`d5a8dc1`](https://github.com/hydephp/hyde/commit/d5a8dc15db87a980144fe3330d778ad69e9e61aa)
- Move app font to vendor [`e43da1d`](https://github.com/hydephp/hyde/commit/e43da1d1845f2837af0273df59f9f8623cce8b2b)
- Remove legacy stubs and test [`8740cc2`](https://github.com/hydephp/hyde/commit/8740cc2996fc9e66df40b2ff6b0f58ec0c32fc34)

#### [v0.2.1-alpha](https://github.com/hydephp/hyde/compare/v0.2.0-alpha...v0.2.1-alpha)

> 21 March 2022

- Add a customizable footer [`#31`](https://github.com/hydephp/hyde/pull/31)
- Adds a customizable footer [`09813cf`](https://github.com/hydephp/hyde/commit/09813cf6810a8e94e8b4301e9c460aa794ad656d)
- Clarify comments in configuration file [`09a7e64`](https://github.com/hydephp/hyde/commit/09a7e6480db8ff72af02db85d160c656ca19fee7)
- Compile frontend assets [`fdb68d5`](https://github.com/hydephp/hyde/commit/fdb68d537e7dd671c033c295815a7c9994e5381b)

#### [v0.2.0-alpha](https://github.com/hydephp/hyde/compare/v0.1.1-pre.patch...v0.2.0-alpha)

> 21 March 2022

- Add responsive navigation to resolve #7 [`#30`](https://github.com/hydephp/hyde/pull/30)
- Add support for images [`#29`](https://github.com/hydephp/hyde/pull/29)
- Fix bug #22 where the feed was not sorting the posts by date [`#28`](https://github.com/hydephp/hyde/pull/28)
- Overhaul the navigation menu to add configuration options [`#27`](https://github.com/hydephp/hyde/pull/27)
- Improve the front matter parser to fix #21 [`#23`](https://github.com/hydephp/hyde/pull/23)
- Check for the app env in the .env file [`#20`](https://github.com/hydephp/hyde/pull/20)
- Add the Torchlight badge automatically [`#19`](https://github.com/hydephp/hyde/pull/19)
- #14 Add publishable 404 pages [`#18`](https://github.com/hydephp/hyde/pull/18)
- Create Validator command to help catch any issues in the setup [`#17`](https://github.com/hydephp/hyde/pull/17)
- Merge pull request #30 from hydephp/7-feature-make-the-navigation-menu-responsive [`#7`](https://github.com/hydephp/hyde/issues/7)
- Add a navigation menu blacklist, fixes #26 [`#26`](https://github.com/hydephp/hyde/issues/26)
- Fix #25, automatically add link to docs [`#25`](https://github.com/hydephp/hyde/issues/25)
- Merge pull request #23 from hydephp/21-bug-front-matter-parser-not-stripping-quotes [`#21`](https://github.com/hydephp/hyde/issues/21)
- Improve the front matter parser to fix #21 [`#21`](https://github.com/hydephp/hyde/issues/21)
- Fix #15, remove redundant values from created file [`#15`](https://github.com/hydephp/hyde/issues/15)
- Add the stubs [`5416fd2`](https://github.com/hydephp/hyde/commit/5416fd22198cc3e5912911aa8547e3a3aa92f734)
- Add tests [`9284a5a`](https://github.com/hydephp/hyde/commit/9284a5a037c8a6afd72dca12d4109b308a432d0b)
- Implement  #16, add custom navigation links [`1007d0d`](https://github.com/hydephp/hyde/commit/1007d0de0a15064157ad7bf4bb801abfb1d2281e)

#### [v0.1.1-pre.patch](https://github.com/hydephp/hyde/compare/v0.1.1-pre...v0.1.1-pre.patch)

> 19 March 2022

- Patches #12, Sev2 Bug: Compiler not using Markdown [`ad640de`](https://github.com/hydephp/hyde/commit/ad640de7bc603330846e025588ea477df55f3962)

#### [v0.1.1-pre](https://github.com/hydephp/hyde/compare/v0.1.0-pre...v0.1.1-pre)

> 19 March 2022

- Merge 1.x [`#2`](https://github.com/hydephp/hyde/pull/2)
- Fix #6, handle missing docs index [`#6`](https://github.com/hydephp/hyde/issues/6)
- Update installation instructions [`785a450`](https://github.com/hydephp/hyde/commit/785a450c2f72faeb3c87a4863e3a27564eece60a)
- Add command for making arbitrary navigation links [`3970d57`](https://github.com/hydephp/hyde/commit/3970d5712da4478b5ce2adb828e7cedd1b443526)
- Create codeql-analysis.yml [`5a6f7ad`](https://github.com/hydephp/hyde/commit/5a6f7ad1ae218a1138f955faf569fe9aecb54e2f)

#### v0.1.0-pre

> 18 March 2022

- Delete _pages directory [`#1`](https://github.com/hydephp/hyde/pull/1)
- Initial Commit [`109bddb`](https://github.com/hydephp/hyde/commit/109bddb7b6144ba704e283c220754759276f1a23)
- Add Torchlight support [`300e06f`](https://github.com/hydephp/hyde/commit/300e06fc6d8600ed8db1b3407a500b5189236eff)
- Add the Logo [`1d06347`](https://github.com/hydephp/hyde/commit/1d063479460fdb4cf621f606b2beafaa7d7d0c61)

### Hyde/Framework Archive (pre v0.35.0)


All notable changes to this project will be documented in this file. Dates are displayed in UTC.

Generated by [`auto-changelog`](https://github.com/CookPete/auto-changelog).

#### [v0.34.0](https://github.com/hydephp/framework/compare/v0.33.0-beta...v0.34.0)

> 6 June 2022

- Deprecate Hyde::features(), use Hyde::hasFeature() instead [`#523`](https://github.com/hydephp/framework/pull/523)
- Create image link helper, fix #434 [`#522`](https://github.com/hydephp/framework/pull/522)
- Create a PageModel contract and helpers to get parsed model collections [`#521`](https://github.com/hydephp/framework/pull/521)
- Merge pull request #522 from hydephp/create-image-file-object [`#434`](https://github.com/hydephp/framework/issues/434)
- Add image path helper, fix #434 [`#434`](https://github.com/hydephp/framework/issues/434)
- Fix #516 Add Composer validation to the test suite [`#516`](https://github.com/hydephp/framework/issues/516)
- Move the static::all() helper to AbstractPage [`c726ad7`](https://github.com/hydephp/framework/commit/c726ad73cf30eff59bc2425f8c35eacbe499f2e4)
- Create MarkdownPost::latest() [`e6d9e4a`](https://github.com/hydephp/framework/commit/e6d9e4a1b2689e58cef44d7109c0594bc3df972f)
- Implement MarkdownPost::all() [`cda2010`](https://github.com/hydephp/framework/commit/cda201052547935d545869d651bc297f45617011)

#### [v0.33.0-beta](https://github.com/hydephp/framework/compare/v0.32.1-beta...v0.33.0-beta)

> 4 June 2022


#### [v0.32.1-beta](https://github.com/hydephp/framework/compare/v0.32.0-beta...v0.32.1-beta)

> 4 June 2022

- Move back hyde/realtime-compiler to hyde/hyde [`#517`](https://github.com/hydephp/framework/pull/517)
- Update composer.lock [`246da42`](https://github.com/hydephp/framework/commit/246da42b693a07175e861bf653763cfa9af42ec2)
- Update composer.lock [`9e835b6`](https://github.com/hydephp/framework/commit/9e835b6cec2ef64fb81d9378a3bab3c090f460bf)

#### [v0.32.0-beta](https://github.com/hydephp/framework/compare/v0.31.1-beta...v0.32.0-beta)

> 4 June 2022

- Refactor to use Laravel cache helper instead of custom implementation [`#514`](https://github.com/hydephp/framework/pull/514)
- Improve metadata for featured post images [`#512`](https://github.com/hydephp/framework/pull/512)
- Skip generating auxiliary files in the main built loop when there is no underlying content [`#511`](https://github.com/hydephp/framework/pull/511)
- Fix: #506: Move ext-simplexml in composer.json to suggest as it is not a strict dependency [`#510`](https://github.com/hydephp/framework/pull/510)
- Rewrite Realtime Compiler [`#508`](https://github.com/hydephp/framework/pull/508)
- Fix #496: Missing image "contentUrl" metadata [`#496`](https://github.com/hydephp/framework/issues/496)
- Don't create search files when there are no pages [`#482`](https://github.com/hydephp/framework/issues/482)
- Update Hyde Realtime Compiler to v2.0 [`f917319`](https://github.com/hydephp/framework/commit/f917319149bfce3249f9921b6bc3ecf0a6307f42)
- Delete RELEASE-NOTES-DRAFT.md [`9853526`](https://github.com/hydephp/framework/commit/9853526ec23b8fe7a325126d8e740e354a1b4eb2)
- Remove pre-check as package is always included [`076a1be`](https://github.com/hydephp/framework/commit/076a1bef2ae68117d092b38d9ee8d6f2fef64172)

#### [v0.31.1-beta](https://github.com/hydephp/framework/compare/v0.31.0-beta...v0.31.1-beta)

> 3 June 2022


#### [v0.31.0-beta](https://github.com/hydephp/framework/compare/v0.30.1-beta...v0.31.0-beta)

> 2 June 2022

- Fix #499: Make the search dialog positioning fixed [`#503`](https://github.com/hydephp/framework/pull/503)
- Make documentation pages smarter [`#501`](https://github.com/hydephp/framework/pull/501)
- Link to markdown source files [`#498`](https://github.com/hydephp/framework/pull/498)
- Fix #490 Make heading permalinks visible [`#493`](https://github.com/hydephp/framework/pull/493)
- Add Markdown Post/Preprocessors  [`#488`](https://github.com/hydephp/framework/pull/488)
- Merge pull request #503 from hydephp/499-make-the-search-menu-dialog-position-fixed [`#499`](https://github.com/hydephp/framework/issues/499)
- Fix #499: Make the search dialog positioning fixed [`#499`](https://github.com/hydephp/framework/issues/499)
- Merge pull request #493 from hydephp/make-heading-permalinks-visible [`#490`](https://github.com/hydephp/framework/issues/490)
- Fix #490 Make heading permalinks visible [`#490`](https://github.com/hydephp/framework/issues/490)
- Merge unit tests into single feature test [`c455d1c`](https://github.com/hydephp/framework/commit/c455d1c0246d9361fd2115528ce616ea797915ea)
- Use the same static transformation instead of DOM [`bdba273`](https://github.com/hydephp/framework/commit/bdba27386df05a46ca27071601aed1f6f3f00b59)
- Document the edit button feature [`dc0d9d7`](https://github.com/hydephp/framework/commit/dc0d9d750e0b61701557472ebd1ce1b1e556058a)

#### [v0.30.1-beta](https://github.com/hydephp/framework/compare/v0.30.0-beta...v0.30.1-beta)

> 31 May 2022

- Fix support for outputting documentation pages to root output directory [`#480`](https://github.com/hydephp/framework/pull/480)
- Fix https://github.com/hydephp/framework/issues/462#issuecomment-1142408337 [`#462`](https://github.com/hydephp/framework/issues/462)
- Fix bug #462 caused by trailing slash in docs path [`6be5055`](https://github.com/hydephp/framework/commit/6be5055633b4ef9be358fdc82dfcc5fc1aad068b)

#### [v0.30.0-beta](https://github.com/hydephp/framework/compare/v0.29.5-beta...v0.30.0-beta)

> 31 May 2022

- Add inline Blade support to markdown [`#478`](https://github.com/hydephp/framework/pull/478)
- Create page and document Blade-supported Markdown [`0d7ae0f`](https://github.com/hydephp/framework/commit/0d7ae0f213eba74a61257f9207f184169a36127d)
- Add base tests [`ae4b0dc`](https://github.com/hydephp/framework/commit/ae4b0dc24568483ed2be4330c290839f4382571a)
- Sketch out the service class [`4b88214`](https://github.com/hydephp/framework/commit/4b8821447f7375d2cae20a685b76a8102972ee40)

#### [v0.29.5-beta](https://github.com/hydephp/framework/compare/v0.29.4-beta...v0.29.5-beta)

> 31 May 2022

- Bump HydeFront to v1.10 [`0f28947`](https://github.com/hydephp/framework/commit/0f28947f2b197177b1b30626d161408d46f71335)

#### [v0.29.4-beta](https://github.com/hydephp/framework/compare/v0.29.3-beta...v0.29.4-beta)

> 30 May 2022

- Add color-scheme meta, fix #460 [`#460`](https://github.com/hydephp/framework/issues/460)
- Try to figure out why Codecov is not working [`9d3371c`](https://github.com/hydephp/framework/commit/9d3371cab5606c280261c2f3e209beeff3289f5a)
- Revert codecov changes [`b253969`](https://github.com/hydephp/framework/commit/b2539690fa4d013b06082a162f05816eff99e6bd)

#### [v0.29.3-beta](https://github.com/hydephp/framework/compare/v0.29.2-beta...v0.29.3-beta)

> 30 May 2022

- Fix Bug #471: og:title and twitter:title should use the page title, and only use config one as fallback [`#473`](https://github.com/hydephp/framework/pull/473)
- Fix bug #471, make title metadata dynamic [`b9ac1c8`](https://github.com/hydephp/framework/commit/b9ac1c8d1fa0c484d2d95fad891c2c3c5c7f039c)
- Make dynamic meta title use title property instead [`6aaa612`](https://github.com/hydephp/framework/commit/6aaa612b80600ae4ef8136fb779623b66206119b)

#### [v0.29.2-beta](https://github.com/hydephp/framework/compare/v0.29.1-beta...v0.29.2-beta)

> 30 May 2022

- Add !important to style override [`3e28b1d`](https://github.com/hydephp/framework/commit/3e28b1dcd91802596edf5dfa454fe2178432688f)

#### [v0.29.1-beta](https://github.com/hydephp/framework/compare/v0.29.0-beta...v0.29.1-beta)

> 30 May 2022

- Use the config defined output path [`927072e`](https://github.com/hydephp/framework/commit/927072e725624d39239845a681e744e2d309694c)
- Update Readme heading to "The Core Framework" [`7a89486`](https://github.com/hydephp/framework/commit/7a89486509ea68071cb5268956dd771766bf327a)

#### [v0.29.0-beta](https://github.com/hydephp/framework/compare/v0.28.1-beta...v0.29.0-beta)

> 30 May 2022

- Load HydeFront v1.9.x needed for HydeSearch [`#468`](https://github.com/hydephp/framework/pull/468)
- Make the search feature configurable and toggleable [`#467`](https://github.com/hydephp/framework/pull/467)
- Add the HydeSearch frontend integration for documentation pages [`#465`](https://github.com/hydephp/framework/pull/465)
- Create the backend search index generation for documentation pages [`#459`](https://github.com/hydephp/framework/pull/459)
- Bump guzzlehttp/guzzle from 7.4.2 to 7.4.3 [`#456`](https://github.com/hydephp/framework/pull/456)
- Refactor inline styles to HydeFront Sass [`86fff1d`](https://github.com/hydephp/framework/commit/86fff1d9dc7f5d5e5ca171cf79517af0d2fb1639)
- Begin sketching out the class [`ed131bd`](https://github.com/hydephp/framework/commit/ed131bd4196afbcf1684bb999c5a7fe98d1948b8)
- Extract search widget to component [`420f662`](https://github.com/hydephp/framework/commit/420f662a3040cc4c0f3f8e48d6a350686fb02803)

#### [v0.28.1-beta](https://github.com/hydephp/framework/compare/v0.28.0-beta-pre...v0.28.1-beta)

> 25 May 2022

- Fix #450: Add custom exceptions [`#454`](https://github.com/hydephp/framework/pull/454)
- Refactor author configuration system [`#449`](https://github.com/hydephp/framework/pull/449)
- Merge pull request #454 from hydephp/450-add-custom-exceptions [`#450`](https://github.com/hydephp/framework/issues/450)
- Remove AuthorService [`9f9d64d`](https://github.com/hydephp/framework/commit/9f9d64dc4f232e6c0a695088cd43dc28f8535fc3)
- Clean up code [`f8452b9`](https://github.com/hydephp/framework/commit/f8452b9d697505733f147bf3f59e92abfb307727)
- Create FileConflictException [`02d534c`](https://github.com/hydephp/framework/commit/02d534cd801ed19fac264373685c30b3f6858c34)

#### [v0.28.0-beta-pre](https://github.com/hydephp/framework/compare/v0.28.0-beta...v0.28.0-beta-pre)

> 22 May 2022

#### [v0.28.0-beta](https://github.com/hydephp/framework/compare/v0.27.12-beta...v0.28.0-beta)

> 23 May 2022

- Refactor author configuration system [`#449`](https://github.com/hydephp/framework/pull/449)
- Refactor configuration to use snake_case for all options, and extract documentation settings to own file [`#444`](https://github.com/hydephp/framework/pull/444)
- Remove AuthorService [`9f9d64d`](https://github.com/hydephp/framework/commit/9f9d64dc4f232e6c0a695088cd43dc28f8535fc3)
- Extract documentation configuration options to docs.php [`92b9ae5`](https://github.com/hydephp/framework/commit/92b9ae5fc4f2c7743206ebcfce48d81e4df7746d)
- Use the snake_case config format [`f578855`](https://github.com/hydephp/framework/commit/f578855047113c3181c9869f1ec9d4d521c3bd62)

#### [v0.27.12-beta](https://github.com/hydephp/framework/compare/v0.27.11-beta...v0.27.12-beta)

> 22 May 2022

- Code cleanup without affecting functionality  [`#440`](https://github.com/hydephp/framework/pull/440)
- Add missing return type declarations [`684b792`](https://github.com/hydephp/framework/commit/684b792796e330c958a312d914057771eb72f2da)
- Add PHPDoc comments with @throws tags [`ae44806`](https://github.com/hydephp/framework/commit/ae44806cb3c23249bc68a39bd1ede6fa0c4e8e56)

#### [v0.27.11-beta](https://github.com/hydephp/framework/compare/v0.27.10-beta...v0.27.11-beta)

> 21 May 2022

- Fix #429: Add page priorities to sitemap generation [`#437`](https://github.com/hydephp/framework/pull/437)
- Merge pull request #437 from hydephp/add-dynamic-page-priorities-for-sitemap [`#429`](https://github.com/hydephp/framework/issues/429)
- Add page priority support [`0bfbbba`](https://github.com/hydephp/framework/commit/0bfbbba07fd8d1720fe6a693089e62dbc0dc018a)

#### [v0.27.10-beta](https://github.com/hydephp/framework/compare/v0.27.9-beta...v0.27.10-beta)

> 20 May 2022

- Improve RSS image handling and feed and sitemap generation processes [`#435`](https://github.com/hydephp/framework/pull/435)
- Create HydeBuildRssFeedCommand.php [`ac4788f`](https://github.com/hydephp/framework/commit/ac4788f987cb517d51a6d0a4fddc5684777c9a0a)
- Create build:sitemap command [`82c73a3`](https://github.com/hydephp/framework/commit/82c73a392350dff171b496220d8d1f70d363102d)
- Fetch information for local images [`a10c1c3`](https://github.com/hydephp/framework/commit/a10c1c361852154e9eb52947b003a65ede09c3ef)

#### [v0.27.9-beta](https://github.com/hydephp/framework/compare/v0.27.8-beta...v0.27.9-beta)

> 20 May 2022

- Rename and restructure internal hooks [`0562ae3`](https://github.com/hydephp/framework/commit/0562ae3558363afddfeb63a7148f967940ed4966)
- Update test code formatting [`1a9dcaf`](https://github.com/hydephp/framework/commit/1a9dcaf670a9757985013c7c3a3e01fa93f75579)
- Add sitemap link test [`9ba7b10`](https://github.com/hydephp/framework/commit/9ba7b109560881867ee9ba81a5e37bb10b370616)

#### [v0.27.8-beta](https://github.com/hydephp/framework/compare/v0.27.7-beta...v0.27.8-beta)

> 19 May 2022

- Update the tests [`a80593e`](https://github.com/hydephp/framework/commit/a80593e1ac6fc79c3d78ea2d736c89955e6b6805)

#### [v0.27.7-beta](https://github.com/hydephp/framework/compare/v0.27.6-beta...v0.27.7-beta)

> 19 May 2022

- Normalize the site URL [`a4b9ce7`](https://github.com/hydephp/framework/commit/a4b9ce7a32321e3e67df5aaed477fbfc54c6c524)

#### [v0.27.6-beta](https://github.com/hydephp/framework/compare/v0.27.5-beta...v0.27.6-beta)

> 19 May 2022

- Add deployment documentation [`4b188f2`](https://github.com/hydephp/framework/commit/4b188f20848e87cd3b3e77af9cdde5b373e2e4d3)
- Merge sections to be more compact [`baadd48`](https://github.com/hydephp/framework/commit/baadd4891d719123720f8bc79a1a82a4837e547e)
- Restructure document flow [`40f4a3d`](https://github.com/hydephp/framework/commit/40f4a3d37b835b40b392f5f72a4ab46563df5042)

#### [v0.27.5-beta](https://github.com/hydephp/framework/compare/v0.27.4-beta...v0.27.5-beta)

> 19 May 2022

- Fix bug where categorized documentation sidebar items were not sorted [`#422`](https://github.com/hydephp/framework/pull/422)
- Fix #367: Add upcoming documentation files [`#367`](https://github.com/hydephp/framework/issues/367)
- Create building-your-site.md [`6989bd5`](https://github.com/hydephp/framework/commit/6989bd59d33d84cebf3e0ef134f4107d149c6fd5)
- Update documentation page orders [`b38c58b`](https://github.com/hydephp/framework/commit/b38c58bba32312d932d1a005b3015b3ce9dd7329)

#### [v0.27.4-beta](https://github.com/hydephp/framework/compare/v0.27.3-beta...v0.27.4-beta)

> 19 May 2022

- Fix #419: Add meta links to the RSS feed [`#419`](https://github.com/hydephp/framework/issues/419)
- Refactor internal helpers to be public static [`283e5d2`](https://github.com/hydephp/framework/commit/283e5d2154862f114e82f1e5e036924d449e7ebf)
- Add page slug for compatibility, fixing bug where Blade pages did not get canonical link tags [`d3ac8e4`](https://github.com/hydephp/framework/commit/d3ac8e492bb01ba538111ba8c7f4dfb48cbc5785)

#### [v0.27.3-beta](https://github.com/hydephp/framework/compare/v0.27.2-beta...v0.27.3-beta)

> 19 May 2022

- Add unit test for fluent Markdown post helpers [`2a3b90b`](https://github.com/hydephp/framework/commit/2a3b90bbf2ffab9709a49447b9a4aa80cd14ca9e)
- Add Author::getName() unit test [`64616a6`](https://github.com/hydephp/framework/commit/64616a6d24d8335e890bde35c8fafa37ef9bb4ba)
- Change RSS feed default filename to feed.xml [`d545b07`](https://github.com/hydephp/framework/commit/d545b07130cb58c42cb9701b3c2322ac133e617e)

#### [v0.27.2-beta](https://github.com/hydephp/framework/compare/v0.27.1-beta...v0.27.2-beta)

> 19 May 2022

- Add RSS feed for Markdown blog posts [`#413`](https://github.com/hydephp/framework/pull/413)
- Add the RSSFeedService test [`a21596f`](https://github.com/hydephp/framework/commit/a21596f68792d313c551789f713950a6c2410975)
- Add the initial channel items [`9cb9b30`](https://github.com/hydephp/framework/commit/9cb9b302662de3d1dc80ba0ea09a48c3a53f2e78)
- Update sitemap tests and add rss feed tests [`fe93f5b`](https://github.com/hydephp/framework/commit/fe93f5b7cd1dea1f3bbb5a851b8185e5288f50de)

#### [v0.27.1-beta](https://github.com/hydephp/framework/compare/v0.27.0-beta...v0.27.1-beta)

> 18 May 2022

- Fix #403: Remove @HydeConfigVersion annotation from config/hyde.php [`#408`](https://github.com/hydephp/framework/pull/408)
- Merge pull request #408 from hydephp/remove-hydeconfigversion-annotation-from-hyde-config [`#403`](https://github.com/hydephp/framework/issues/403)
- Remove HydeConfigVersion annotation [`84b1602`](https://github.com/hydephp/framework/commit/84b1602fc3280ef66637799c8aaa9d9513c3142c)

#### [v0.27.0-beta](https://github.com/hydephp/framework/compare/v0.26.0-beta...v0.27.0-beta)

> 18 May 2022

- Add sitemap.xml generation [`#404`](https://github.com/hydephp/framework/pull/404)
- Add SitemapService tests [`ce5d8ed`](https://github.com/hydephp/framework/commit/ce5d8ed089546a8262e637d3ce399bf190672ba0)
- Refactor shared code into new helper [`46f41d6`](https://github.com/hydephp/framework/commit/46f41d6848a5562006f4290aa00df221d25d815a)
- Create basic sitemap generator [`1f66928`](https://github.com/hydephp/framework/commit/1f669282d727042df5074f0182bf5e0563d07a91)

#### [v0.26.0-beta](https://github.com/hydephp/framework/compare/v0.25.0-beta...v0.26.0-beta)

> 18 May 2022

- Fix #398: Remove the deprecated Metadata model [`#400`](https://github.com/hydephp/framework/pull/400)
- Fix #379: Extract menu logo to component [`#396`](https://github.com/hydephp/framework/pull/396)
- Update helper namespaces [`#395`](https://github.com/hydephp/framework/pull/395)
- Fix #385: Move page parsers into models/parsers namespace [`#394`](https://github.com/hydephp/framework/pull/394)
- Remove redundancy and merge Meta and Metadata models #384 [`#390`](https://github.com/hydephp/framework/pull/390)
- Unify the $page property and add a fluent metadata helper  [`#388`](https://github.com/hydephp/framework/pull/388)
- Merge pull request #400 from hydephp/398-remove-legacy-metadata-model [`#398`](https://github.com/hydephp/framework/issues/398)
- Merge pull request #396 from hydephp/extract-navigation-menu-logo-to-component-to-make-it-easier-to-customize [`#379`](https://github.com/hydephp/framework/issues/379)
- Fix #379: Extract menu logo to component [`#379`](https://github.com/hydephp/framework/issues/379) [`#379`](https://github.com/hydephp/framework/issues/379)
- Merge pull request #394 from hydephp/385-move-page-parsers-into-a-namespace [`#385`](https://github.com/hydephp/framework/issues/385)
- Fix #385: Move page parsers into a namespace [`#385`](https://github.com/hydephp/framework/issues/385)
- Fix #382: Unify the $page property [`#382`](https://github.com/hydephp/framework/issues/382)
- Fix #375, Add config option to add og:properties [`#375`](https://github.com/hydephp/framework/issues/375)
- Extract metadata helpers to concern [`72b1356`](https://github.com/hydephp/framework/commit/72b1356298ae0537356a88630e144df07fc6adf8)
- Add test for, and improve Meta helper [`15ccd27`](https://github.com/hydephp/framework/commit/15ccd271706ddf38f8011287ed28f04a60cd4076)
- Refactor concern to not be dependent on Metadata model [`b247bb0`](https://github.com/hydephp/framework/commit/b247bb0627dc481c08bb8e47f7c38ec57816154a)

#### [v0.25.0-beta](https://github.com/hydephp/framework/compare/v0.24.0-beta...v0.25.0-beta)

> 17 May 2022

- Load asset service from the service container [`#373`](https://github.com/hydephp/framework/pull/373)
- Rename --pretty option to --run-prettier to distinguish it better in build command  [`#368`](https://github.com/hydephp/framework/pull/368)
- Allow site output directory to be customized [`#362`](https://github.com/hydephp/framework/pull/362)
- Configuration and autodiscovery improvements [`#340`](https://github.com/hydephp/framework/pull/340)
- Add configurable "pretty URLs" [`#354`](https://github.com/hydephp/framework/pull/354)
- Add sidebar config offset, fix #307 [`#348`](https://github.com/hydephp/framework/pull/348)
- Change BuildService to DiscoveryService [`#347`](https://github.com/hydephp/framework/pull/347)
- Fix #361 Rename --pretty option to --run-prettier [`#361`](https://github.com/hydephp/framework/issues/361)
- Fix #350, Use the model path properties [`#350`](https://github.com/hydephp/framework/issues/350)
- Add option for pretty urls fix #330 [`#330`](https://github.com/hydephp/framework/issues/330)
- Rewrite index docs path to pretty url, fix #353 [`#353`](https://github.com/hydephp/framework/issues/353)
- Fix #330, Create helper to make pretty URLs if enabled [`#330`](https://github.com/hydephp/framework/issues/330)
- Merge pull request #348 from hydephp/add-sidebar-priority-offset-for-config-defined-values [`#307`](https://github.com/hydephp/framework/issues/307)
- Add sidebar config offset, fix #307 [`#307`](https://github.com/hydephp/framework/issues/307)
- Fix #343 [`#343`](https://github.com/hydephp/framework/issues/343)
- Restructure the tests [`41bd056`](https://github.com/hydephp/framework/commit/41bd0560fb014e3a042909e3162e2a2da28c0b77)
- Add helpers to make it easier to refactor source paths [`10e145e`](https://github.com/hydephp/framework/commit/10e145ea345d2aca22c81ec15d7af073c5ee803c)
- Utalize the $sourceDirectory property in build services [`9d9cbff`](https://github.com/hydephp/framework/commit/9d9cbff800d1422461dfcee6f3983662c51c5606)

#### [v0.24.0-beta](https://github.com/hydephp/framework/compare/v0.23.5-beta...v0.24.0-beta)

> 11 May 2022

- Add documentation sidebar category labels, fixes #309 [`#326`](https://github.com/hydephp/framework/pull/326)
- Merge pull request #326 from hydephp/309-add-documentation-sidebar-category-labels [`#309`](https://github.com/hydephp/framework/issues/309)
- Sketch out the files for the category integration [`d6c81bb`](https://github.com/hydephp/framework/commit/d6c81bbcce78f0d72f131f49e1c61716e0cd26d6)
- Implement category creation [`70448b1`](https://github.com/hydephp/framework/commit/70448b14ac6d8be3c8162ec78d12901f7a5c7579)
- Set category of uncategorized items [`9f0feb3`](https://github.com/hydephp/framework/commit/9f0feb364a0fa8be9401a5453d8a1ded4b0ae40a)

#### [v0.23.5-beta](https://github.com/hydephp/framework/compare/v0.23.4-beta...v0.23.5-beta)

> 11 May 2022

- Add back skip to content button to Lagrafo docs layout, fix #300 [`#322`](https://github.com/hydephp/framework/pull/322)
- Change max prose width of markdown pages to match blog posts, fix #303 [`#321`](https://github.com/hydephp/framework/pull/321)
- Fix #153, bug where config option uses app name instead of Hyde name. [`#320`](https://github.com/hydephp/framework/pull/320)
- Add option to mark site as installed, fix #289 [`#289`](https://github.com/hydephp/framework/issues/289)
- Merge pull request #322 from hydephp/300-add-back-skip-to-content-button-to-lagrafo-docs-layout [`#300`](https://github.com/hydephp/framework/issues/300)
- Add skip to content button docs layout, fix #300 [`#300`](https://github.com/hydephp/framework/issues/300)
- Merge pull request #321 from hydephp/303-change-max-width-of-markdown-pages-to-match-blog-posts [`#303`](https://github.com/hydephp/framework/issues/303)
- Change max width to match blog posts, fix #303 [`#303`](https://github.com/hydephp/framework/issues/303)
- Merge pull request #320 from hydephp/294-fix-bug-where-config-option-uses-app-name-instead-of-hyde-name [`#153`](https://github.com/hydephp/framework/issues/153)
- #153 Fix bug where config option uses app name instead of Hyde name. [`c90977c`](https://github.com/hydephp/framework/commit/c90977cf942cad214b8ea8218be3d5773d1fc633)
- Update install command for new site name syntax [`0687351`](https://github.com/hydephp/framework/commit/06873511064dd2b5ed2faa6ff1ad87c3210185ea)

#### [v0.23.4-beta](https://github.com/hydephp/framework/compare/v0.23.3-beta...v0.23.4-beta)

> 11 May 2022

- Refactor post excerpt component to be less reliant on directly using front matter and add view test [`#318`](https://github.com/hydephp/framework/pull/318)
- Formatting: Add newline after console output when running build without API calls, fix #313 [`#316`](https://github.com/hydephp/framework/pull/316)
- Fix #314, add background color fallback to documentation page body [`#315`](https://github.com/hydephp/framework/pull/315)
- Restructure and format component, fix #306 [`#306`](https://github.com/hydephp/framework/issues/306)
- Merge pull request #316 from hydephp/313-formatting-add-newline-after-disabling-external-api-calls-in-build-command [`#313`](https://github.com/hydephp/framework/issues/313)
- Formatting: Add newline after --no-api info, fix #313 [`#313`](https://github.com/hydephp/framework/issues/313)
- Merge pull request #315 from hydephp/314-add-dark-mode-background-to-body-in-documentation-pages-to-prevent-fouc [`#314`](https://github.com/hydephp/framework/issues/314)
- Fix #314, add background color fallback to docs body [`#314`](https://github.com/hydephp/framework/issues/314)
- Implement hidden: true front matter to hide documentation pages from sidebar, fix #310 [`#310`](https://github.com/hydephp/framework/issues/310)
- Create ArticleExcerptViewTest.php [`4a3ecaa`](https://github.com/hydephp/framework/commit/4a3ecaa02134583c36d3b8685fa5005f586f4293)
- Add tests for the fluent date-author string [`30f7f67`](https://github.com/hydephp/framework/commit/30f7f6762c6481c148908c26a5930f6e2daf1d80)

#### [v0.23.3-beta](https://github.com/hydephp/framework/compare/v0.23.2-beta...v0.23.3-beta)

> 10 May 2022

- Fix #310, allow documentation pages to be hidden from sidebar using front matter [`#311`](https://github.com/hydephp/framework/pull/311)
- Merge pull request #311 from hydephp/310-implement-hidden-true-front-matter-to-hide-documentation-pages-from-sidebar [`#310`](https://github.com/hydephp/framework/issues/310)
- Fix #310, allow items to be hidden from sidebar with front matter [`#310`](https://github.com/hydephp/framework/issues/310)

#### [v0.23.2-beta](https://github.com/hydephp/framework/compare/v0.23.1-beta...v0.23.2-beta)

> 7 May 2022

- Refactor documentation sidebar internals [`#299`](https://github.com/hydephp/framework/pull/299)
- Create feature test for the new sidebar service [`0adf948`](https://github.com/hydephp/framework/commit/0adf94889c36e0b77fb63018221b16c7f1fc8374)
- Remove deprecated action [`063a85a`](https://github.com/hydephp/framework/commit/063a85aa8979fa5780ba5622c9d9f395c2c159b3)
- Create the sidebar models [`fbcae7c`](https://github.com/hydephp/framework/commit/fbcae7cacd100267440b362a97f97d7bbdee09a9)

#### [v0.23.1-beta](https://github.com/hydephp/framework/compare/v0.23.0-beta...v0.23.1-beta)

> 6 May 2022

- Add the test helper files [`3cd5a56`](https://github.com/hydephp/framework/commit/3cd5a56aec24fde17bc1a40c6760d6fc24db3113)
- Test description has warning for out of date config [`a90c0b1`](https://github.com/hydephp/framework/commit/a90c0b17663683737cea8fa75dd3d3d39e743f66)
- Delete .run directory [`8cd71fc`](https://github.com/hydephp/framework/commit/8cd71fc4f98efb514c9995a665e5f47f839fa940)

#### [v0.23.0-beta](https://github.com/hydephp/framework/compare/v0.22.0-beta...v0.23.0-beta)

> 6 May 2022

- Refactor docs layout to use Lagrafo instead of Laradocgen [`#292`](https://github.com/hydephp/framework/pull/292)
- Port lagrafo (wip) [`6ca2309`](https://github.com/hydephp/framework/commit/6ca230964211c79fe19df5954a65ad846500ba5e)
- Move all head tags into blade component [`3093ebf`](https://github.com/hydephp/framework/commit/3093ebf65556e185649a40fd8459caa3fa250d7d)
- Use the Hyde layout [`e09e301`](https://github.com/hydephp/framework/commit/e09e301dba196f6d3336a3f9cf8a265c8939af6c)

#### [v0.22.0-beta](https://github.com/hydephp/framework/compare/v0.21.6-beta...v0.22.0-beta)

> 5 May 2022

- Update HydeFront version to v1.5.x [`#287`](https://github.com/hydephp/framework/pull/287)
- Refactor script interactions [`#286`](https://github.com/hydephp/framework/pull/286)
- Hide the install command once it has been run, fix #280 [`#280`](https://github.com/hydephp/framework/issues/280)
- Hide the install command once it has been run, fix #280 [`#280`](https://github.com/hydephp/framework/issues/280)
- Replace onclick with element IDs [`e97d545`](https://github.com/hydephp/framework/commit/e97d5457117e4980425d12fea97bb0dc81eae904)
- Move dark mode switch [`9f6fdf8`](https://github.com/hydephp/framework/commit/9f6fdf83561f4f4e1f8d2e5d4b44e0a923963c94)

#### [v0.21.6-beta](https://github.com/hydephp/framework/compare/v0.21.5-beta...v0.21.6-beta)

> 4 May 2022

- Create installer command, fix #149 [`#279`](https://github.com/hydephp/framework/pull/279)
- Merge pull request #279 from hydephp/149-create-installer-command [`#149`](https://github.com/hydephp/framework/issues/149)
- Create Install command that can publish a homepage [`b890eb7`](https://github.com/hydephp/framework/commit/b890eb790fddc7ad8e23785b3677e304343b6616)
- Use installer to set the site name in config [`3f0c843`](https://github.com/hydephp/framework/commit/3f0c843955b8dbfa0cc14879771c50397670cae0)
- Use installer to set the site URL in config [`d5f56ac`](https://github.com/hydephp/framework/commit/d5f56ac20d82eb362363b382695c016157f66e42)

#### [v0.21.5-beta](https://github.com/hydephp/framework/compare/v0.21.4-beta...v0.21.5-beta)

> 3 May 2022

- Update the test to fix updated exception output and remove comments [`cd5a70d`](https://github.com/hydephp/framework/commit/cd5a70d3f8a7b9cf0d97e584191d35ebc642cf5a)

#### [v0.21.4-beta](https://github.com/hydephp/framework/compare/v0.21.3-beta...v0.21.4-beta)

> 3 May 2022

- Fix #231 [`#231`](https://github.com/hydephp/framework/issues/231)

#### [v0.21.3-beta](https://github.com/hydephp/framework/compare/v0.21.2-beta...v0.21.3-beta)

> 3 May 2022

- Allow documentation pages to be scaffolded using the make:page command [`#273`](https://github.com/hydephp/framework/pull/273)
- Allow documentation pages to be scaffolded using the command [`7bbe012`](https://github.com/hydephp/framework/commit/7bbe0123f0e7b609954ca8e52216d19453c96f1a)

#### [v0.21.2-beta](https://github.com/hydephp/framework/compare/v0.21.1-beta...v0.21.2-beta)

> 3 May 2022

- Send a non-intrusive warning when the config file is out of date [`#270`](https://github.com/hydephp/framework/pull/270)
- Create crude action to check if a config file is up to date [`e31210f`](https://github.com/hydephp/framework/commit/e31210f055dea4ca6d76750b9b2ad24c61c05850)
- Create FileCacheServiceTest [`d9141cc`](https://github.com/hydephp/framework/commit/d9141cca4125c055f927c53edf7bf2b7bde9c9d0)
- Add the test [`ee4a64d`](https://github.com/hydephp/framework/commit/ee4a64d9a22314339b002bbd856b2f79c08bffea)

#### [v0.21.1-beta](https://github.com/hydephp/framework/compare/v0.21.0-beta...v0.21.1-beta)

> 3 May 2022

- Create filecache at runtime instead of relying on a JSON file that needs to be up to date [`#265`](https://github.com/hydephp/framework/pull/265)
- Create the filecache at runtime, resolves #243, #246 [`#243`](https://github.com/hydephp/framework/issues/243)
- Remove deprecated filecache store and generator [`7a1eb32`](https://github.com/hydephp/framework/commit/7a1eb32aae22f749611ed95bc6b2fb1fce36bd20)
- Remove "Update Filecache" workflow [`81564c0`](https://github.com/hydephp/framework/commit/81564c0d19ca6d622a4949830e2007ed10731e99)
- Remove legacy try/catch [`34733dd`](https://github.com/hydephp/framework/commit/34733ddfb5a53463688ae20f1357f09b8aec33f2)

#### [v0.21.0-beta](https://github.com/hydephp/framework/compare/v0.20.0-beta...v0.21.0-beta)

> 3 May 2022

- Always empty the _site directory when running the static site build command [`#262`](https://github.com/hydephp/framework/pull/262)
- Always purge output directory when running builder [`a86ad7d`](https://github.com/hydephp/framework/commit/a86ad7d56cbe42bc4541224d951cdf349b5a84ed)

#### [v0.20.0-beta](https://github.com/hydephp/framework/compare/v0.19.0-beta...v0.20.0-beta)

> 2 May 2022

- Update Filecache [`#258`](https://github.com/hydephp/framework/pull/258)
- Remove HydeFront from being bundled as a subrepo [`#257`](https://github.com/hydephp/framework/pull/257)
- Change the action used to create pull requests [`#255`](https://github.com/hydephp/framework/pull/255)
- Exclude files starting with an  underscore from being compiled into pages, fix #220 [`#254`](https://github.com/hydephp/framework/pull/254)
- Create .gitattributes, fixes #223 [`#250`](https://github.com/hydephp/framework/pull/250)
- Deprecate filecache.json and related services [`#248`](https://github.com/hydephp/framework/pull/248)
- Allow documentation sidebar header name to be changed [`#245`](https://github.com/hydephp/framework/pull/245)
- Update Filecache [`#242`](https://github.com/hydephp/framework/pull/242)
- Fix bugs in article and excerpts not fluently constructing descriptions [`#241`](https://github.com/hydephp/framework/pull/241)
- Handle undefined array key title in article-excerpt.blade.php  [`#238`](https://github.com/hydephp/framework/pull/238)
- Fix test matrix not fetching proper branch on PRs [`#235`](https://github.com/hydephp/framework/pull/235)
- Fix sidebar ordering bug by using null coalescing operator instead of elvis operator [`#234`](https://github.com/hydephp/framework/pull/234)
- Add unit test for hasDarkmode, fix #259 [`#259`](https://github.com/hydephp/framework/issues/259)
- Add the test, resolves #259 [`#259`](https://github.com/hydephp/framework/issues/259)
- Merge pull request #254 from hydephp/220-exclude-files-starting-with-an-_underscore-from-being-compiled-into-pages [`#220`](https://github.com/hydephp/framework/issues/220)
- Merge pull request #250 from hydephp/add-gitattributes [`#223`](https://github.com/hydephp/framework/issues/223)
- Create .gitattributes, fixes #223 [`#223`](https://github.com/hydephp/framework/issues/223)
- Make category nullable, fixes #230 [`#230`](https://github.com/hydephp/framework/issues/230)
- Fix #240 [`#240`](https://github.com/hydephp/framework/issues/240)
- Handle undefined array key, fixes #229 [`#229`](https://github.com/hydephp/framework/issues/229)
- Remove the HydeFront subrepo [`d406202`](https://github.com/hydephp/framework/commit/d406202d5f24d0cb543ac02fd2b9dc980c86d966)
- Add test to ensure that post front matter can be omitted [`875c6d4`](https://github.com/hydephp/framework/commit/875c6d46b822a7e5d02b1f281ca00189a222d06b)
- Exclude files starting with an _underscore from being discovered [`0dcdcb6`](https://github.com/hydephp/framework/commit/0dcdcb6a35969094533429345a0108915db388f4)

#### [v0.19.0-beta](https://github.com/hydephp/framework/compare/v0.18.0-beta...v0.19.0-beta)

> 1 May 2022

- Update Filecache [`#226`](https://github.com/hydephp/framework/pull/226)
- Add config option to disable dark mode [`#225`](https://github.com/hydephp/framework/pull/225)
- Update Filecache [`#222`](https://github.com/hydephp/framework/pull/222)
- Refactor assets managing, allowing for Laravel Mix, removing CDN support for Tailwind [`#221`](https://github.com/hydephp/framework/pull/221)
- Fix #211 [`#211`](https://github.com/hydephp/framework/issues/211)
- Add test and clean up docs for HasMetadata [`976cb6c`](https://github.com/hydephp/framework/commit/976cb6c39c2bc7fffcbe160987fa8ba08146f9b0)
- Revert "Update update-filecache.yml" [`abc21e7`](https://github.com/hydephp/framework/commit/abc21e7fcf07d28dc09b99afdafd2764c131936c)
- Update update-filecache.yml [`c25196a`](https://github.com/hydephp/framework/commit/c25196aebb77e8f052a604681523b54f3fc978b7)

#### [v0.18.0-beta](https://github.com/hydephp/framework/compare/v0.17.0-beta...v0.18.0-beta)

> 29 April 2022

- Update Filecache [`#201`](https://github.com/hydephp/framework/pull/201)
- Update Filecache [`#199`](https://github.com/hydephp/framework/pull/199)
- Update Filecache [`#197`](https://github.com/hydephp/framework/pull/197)
- Change priority of stylesheets [`#195`](https://github.com/hydephp/framework/pull/195)
- Update Filecache [`#194`](https://github.com/hydephp/framework/pull/194)
- Switch jsDelivr source to NPM, fix #200 [`#200`](https://github.com/hydephp/framework/issues/200)
- Update dependencies [`b505726`](https://github.com/hydephp/framework/commit/b5057268abd0a9b0aa128cc169e606d1a7a4ebfb)
- Switch to using TypeScript [`6fa9e6c`](https://github.com/hydephp/framework/commit/6fa9e6c4a762f16eac328648d9ad15dc977e4097)
- Create service class to help with #182 [`fb0033c`](https://github.com/hydephp/framework/commit/fb0033c4a9da66e7ee6dcdd9b8a137fe37c82a2f)

#### [v0.17.0-beta](https://github.com/hydephp/framework/compare/v0.16.1-beta...v0.17.0-beta)

> 28 April 2022

- Add the code reports workflow [`#191`](https://github.com/hydephp/framework/pull/191)
- Move test suite actions to framework [`#190`](https://github.com/hydephp/framework/pull/190)
- Merge with master [`#189`](https://github.com/hydephp/framework/pull/189)
- Add matrix tests [`#188`](https://github.com/hydephp/framework/pull/188)
- Move part one of the test suite [`#187`](https://github.com/hydephp/framework/pull/187)
- Move Framework tests from Hyde/Hyde to the Hyde/Framework package [`#185`](https://github.com/hydephp/framework/pull/185)
- Move tests from Hyde to Framework [`22ca673`](https://github.com/hydephp/framework/commit/22ca6731a489b576f578186cd777df4bda9e52d0)
- Format YAML [`e6da9ad`](https://github.com/hydephp/framework/commit/e6da9ada1f83c3e2540dec9f719ce59f2169bcf0)
- Add the workflow [`b20cbd6`](https://github.com/hydephp/framework/commit/b20cbd6c9341c5f0666fdda25ebb472bc512654a)

#### [v0.16.1-beta](https://github.com/hydephp/framework/compare/v0.16.0-beta...v0.16.1-beta)

> 28 April 2022

- Manage asset logic in service class [`c72905f`](https://github.com/hydephp/framework/commit/c72905fcbe8bfd748ec84536e836e8fe154230ec)

#### [v0.16.0-beta](https://github.com/hydephp/framework/compare/v0.15.0-beta...v0.16.0-beta)

> 27 April 2022

- Refactor internal codebase by sorting traits into relevant namespaces [`#175`](https://github.com/hydephp/framework/pull/175)
- Refactor: Move Hyde facade methods to traits [`9b5e4ca`](https://github.com/hydephp/framework/commit/9b5e4ca31a21a858c26c712f73021504ab99b019)
- Refactor: Update namespaces [`96c73aa`](https://github.com/hydephp/framework/commit/96c73aa01946e5f6b862dbf66ffd974d65a3b97f)
- Docs: Remove PHPDocs [`ef2f446`](https://github.com/hydephp/framework/commit/ef2f44604e61e109dcf6d03e96a4ab20cbce8b81)

#### [v0.15.0-beta](https://github.com/hydephp/framework/compare/v0.14.0-beta...v0.15.0-beta)

> 27 April 2022

- Update Filecache [`#170`](https://github.com/hydephp/framework/pull/170)
- Merge HydeFront v1.3.1 [`727c8f3`](https://github.com/hydephp/framework/commit/727c8f3b96f595b6b8a13ba7427106765583ce4c)
- Remove asset publishing commands [`0f49d16`](https://github.com/hydephp/framework/commit/0f49d16105d211df7990ec6f75c042c4bf530071)
- Rework internals, loading styles from CDN [`c5283c0`](https://github.com/hydephp/framework/commit/c5283c011b078a28117477a201ac56a1179dcf1b)

#### [v0.14.0-beta](https://github.com/hydephp/framework/compare/v0.13.0-beta...v0.14.0-beta)

> 21 April 2022

- Update Filecache [`#154`](https://github.com/hydephp/framework/pull/154)
- Change update:resources command signature to update:assets [`#153`](https://github.com/hydephp/framework/pull/153)
- Update Filecache [`#152`](https://github.com/hydephp/framework/pull/152)
- Change resources/frontend to resources/assets [`#151`](https://github.com/hydephp/framework/pull/151)
- Update Filecache [`#148`](https://github.com/hydephp/framework/pull/148)
- Update Filecache [`#147`](https://github.com/hydephp/framework/pull/147)
- Overhaul the Markdown Converter Service to make it easier to customize and extend [`#146`](https://github.com/hydephp/framework/pull/146)
- Refactor to fix https://github.com/hydephp/framework/issues/161 [`#161`](https://github.com/hydephp/framework/issues/161)
- Fix https://github.com/hydephp/framework/issues/156 [`#156`](https://github.com/hydephp/framework/issues/156)
- Move frontend files to resources/assets [`e850367`](https://github.com/hydephp/framework/commit/e85036765df5ce1398da370c50b489bd72bef797)
- Add back asset files [`bd218df`](https://github.com/hydephp/framework/commit/bd218df813c8f1496edc09500016bb21be5164b5)
- Merge with Hydefront [`8b477de`](https://github.com/hydephp/framework/commit/8b477de5793194bb9e5c4c39dee762b0f7934930)

#### [v0.13.0-beta](https://github.com/hydephp/framework/compare/v0.12.0-beta...v0.13.0-beta)

> 20 April 2022

- Update Filecache [`#141`](https://github.com/hydephp/framework/pull/141)
- Add table of contents to the documentation page sidebar [`#140`](https://github.com/hydephp/framework/pull/140)
- Add the table of contents to the frontend [`f728810`](https://github.com/hydephp/framework/commit/f728810ff34cb6a5b9f88552f5ca58b27d61e0dc)
- Add the table of contents generation [`2c4c1b9`](https://github.com/hydephp/framework/commit/2c4c1b9a7a45d527a876474af4c692bdeec1b502)
- Allow table of contents to be disabled in config [`fc9cba1`](https://github.com/hydephp/framework/commit/fc9cba16e92baf584c360e0b6a230a7e99c605e9)

#### [v0.12.0-beta](https://github.com/hydephp/framework/compare/v0.11.0-beta...v0.12.0-beta)

> 19 April 2022

- Update Filecache [`#135`](https://github.com/hydephp/framework/pull/135)
- Update Filecache [`#134`](https://github.com/hydephp/framework/pull/134)
- Allow author array data to be added in front matter [`#133`](https://github.com/hydephp/framework/pull/133)
- Strip front matter from documentation pages [`#130`](https://github.com/hydephp/framework/pull/130)
- Add trait to handle Authors in the data layer [`62f3793`](https://github.com/hydephp/framework/commit/62f3793138a108478a72e8e8176c8ca0c680be20)
- Update the views to move logic to data layer [`2ebc62c`](https://github.com/hydephp/framework/commit/2ebc62c0927ee13e1a01395e59a2316b0f826427)
- Parse the documentation pages using the fileservice [`041bf98`](https://github.com/hydephp/framework/commit/041bf98d8b20b6874cfd8c2edc7fa43bb88d2844)

#### [v0.11.0-beta](https://github.com/hydephp/framework/compare/v0.10.0-beta...v0.11.0-beta)

> 17 April 2022

- Add command for the new realtime compiler [`9be80eb`](https://github.com/hydephp/framework/commit/9be80eb34ed1415654465d4cd1b485d17086f59d)
- Allow the host and port to be specified [`e54a394`](https://github.com/hydephp/framework/commit/e54a394665213d712a6ce30cec98a84045d42738)

#### [v0.10.0-beta](https://github.com/hydephp/framework/compare/v0.9.0-beta...v0.10.0-beta)

> 12 April 2022

- Update Filecache [`#124`](https://github.com/hydephp/framework/pull/124)
- Update Filecache [`#122`](https://github.com/hydephp/framework/pull/122)
- Update Filecache [`#120`](https://github.com/hydephp/framework/pull/120)
- Update Filecache [`#118`](https://github.com/hydephp/framework/pull/118)
- Update Filecache [`#117`](https://github.com/hydephp/framework/pull/117)
- Add darkmode support and refactor blade components [`#116`](https://github.com/hydephp/framework/pull/116)
- Add skip to content link [`#113`](https://github.com/hydephp/framework/pull/113)
- Update the welcome page to be more accessible [`#112`](https://github.com/hydephp/framework/pull/112)
- Remove the deprecated and unused service provider [`#108`](https://github.com/hydephp/framework/pull/108)
- Update Blade components, internal data handling, add a11y features [`#102`](https://github.com/hydephp/framework/pull/102)
- Refactor tests [`#98`](https://github.com/hydephp/framework/pull/98)
- Deprecate internal abstract class HydeBasePublishingCommand [`#97`](https://github.com/hydephp/framework/pull/97)
- Update and simplify the command and rename signature from publish:configs to update:configs, making overwriting files the default. [`#95`](https://github.com/hydephp/framework/pull/95)
- Change blade source directory to _pages [`#90`](https://github.com/hydephp/framework/pull/90)
- Fix line ending sequence issue in checksums [`#86`](https://github.com/hydephp/framework/pull/86)
- Refactor internal file handling logic to be more intelligent to provide a safer, more intuitive, user experience  [`#84`](https://github.com/hydephp/framework/pull/84)
- Fix improper article ID usage - remember to re-publish styles [`#81`](https://github.com/hydephp/framework/pull/81)
- Fix #63, update component to show formatted dates [`#80`](https://github.com/hydephp/framework/pull/80)
- Update Spatie YAML Front Matter Package to fix #36 [`#79`](https://github.com/hydephp/framework/pull/79)
- Add base styles to documentation layout [`#77`](https://github.com/hydephp/framework/pull/77)
- Refactor code to extend base classes and remove shared code [`#74`](https://github.com/hydephp/framework/pull/74)
- Refactor the backend structure of the static page builder command process [`#72`](https://github.com/hydephp/framework/pull/72)
- Supply `_media` as the path argument in the `hyde:rebuild` command to copy all media files. [`#71`](https://github.com/hydephp/framework/pull/71)
- Add more relevant targets for the skip to content link, fix #123 [`#123`](https://github.com/hydephp/framework/issues/123)
- Add the image model, fix #100 [`#100`](https://github.com/hydephp/framework/issues/100)
- Merge pull request #80 from hydephp/63-fix-up-the-post-date-component-to-show-the-readable-name [`#63`](https://github.com/hydephp/framework/issues/63)
- Fix #63, update component to show formatted dates [`#63`](https://github.com/hydephp/framework/issues/63)
- Merge pull request #79 from hydephp/36-spatie-yaml-front-matter-package-not-properly-handling-markdown-documents-with-markdown-inside [`#36`](https://github.com/hydephp/framework/issues/36)
- Compress CSS, 5.48 KB to 3.37 KB (38.56%) [`d7f2054`](https://github.com/hydephp/framework/commit/d7f2054420f6c8a6ac786a705e2a0fc472bc4b92)
- Update dependencies [`f851978`](https://github.com/hydephp/framework/commit/f851978e0e2bf733a933504880333aebfd052fb1)
- Remove the deprecated and now unused base command [`0f137c8`](https://github.com/hydephp/framework/commit/0f137c8303cc6041011b82f80a67906b2bccfc8a)

#### [v0.9.0-beta](https://github.com/hydephp/framework/compare/v0.8.1-beta...v0.9.0-beta)

> 7 April 2022

- Rework how frontend assets (stylesheets and main script) are handled [`#69`](https://github.com/hydephp/framework/pull/69)
- Move the resource files [`7c70467`](https://github.com/hydephp/framework/commit/7c70467499c429d99813e095f0e775bf74ff0c68)
- Add the update frontend resources command [`551df0a`](https://github.com/hydephp/framework/commit/551df0a3813963aaecad3b11e5d7c1f15248241a)
- Add the action to publish the frontend resources [`e2c82fb`](https://github.com/hydephp/framework/commit/e2c82fbc6dda89c6144c949d92f1d8b147f4ab69)

#### [v0.8.1-beta](https://github.com/hydephp/framework/compare/v0.8.0-beta...v0.8.1-beta)

> 3 April 2022

- Add --no-api option to disable Torchlight at runtime, fix #53 [`#53`](https://github.com/hydephp/framework/issues/53)
- Add Changelog.md [`fe2fdf8`](https://github.com/hydephp/framework/commit/fe2fdf8e4e3a43cfcde766ac84bbcbb2c55d4890)
- Create CODE_OF_CONDUCT.md [`9361d1d`](https://github.com/hydephp/framework/commit/9361d1df2615048f01448ab26ef09e5b2de75eb0)
- Create CONTRIBUTING.md [`a581146`](https://github.com/hydephp/framework/commit/a5811466c4ee67ea5ab4b819959ab80984da6770)

#### [v0.8.0-beta](https://github.com/hydephp/framework/compare/v0.7.5-alpha...v0.8.0-beta)

> 2 April 2022

- Rewrite main navigation menu [`#60`](https://github.com/hydephp/framework/pull/60)
- Fix #59, unify sidebar elements [`#59`](https://github.com/hydephp/framework/issues/59)
- Unify the navigation menu [`f0e6cfc`](https://github.com/hydephp/framework/commit/f0e6cfc28eae7c0325a89ab0cce4ab67329e3be5)
- Add the interaction [`c5b4f7e`](https://github.com/hydephp/framework/commit/c5b4f7eb71166bce556b19ddf84861798ea2bda4)

#### [v0.7.5-alpha](https://github.com/hydephp/framework/compare/v0.7.4-alpha...v0.7.5-alpha)

> 2 April 2022

- Fix broken meta url in schema prop [`b54cfe4`](https://github.com/hydephp/framework/commit/b54cfe4a1aa1441584cd0b209fcb89a99fa4ce7a)
- Fix broken meta url in schema prop [`80b5523`](https://github.com/hydephp/framework/commit/80b552305c3d5730a951ae2f5115bed21c9a4b84)

#### [v0.7.4-alpha](https://github.com/hydephp/framework/compare/v0.7.3-alpha...v0.7.4-alpha)

> 1 April 2022

- Fix bug #47 [`b7cdaf6`](https://github.com/hydephp/framework/commit/b7cdaf67e626855c3df7513dd0b58a563f9030be)

#### [v0.7.3-alpha](https://github.com/hydephp/framework/compare/v0.7.2-alpha...v0.7.3-alpha)

> 1 April 2022

- Fix #58 [`#58`](https://github.com/hydephp/framework/issues/58)

#### [v0.7.2-alpha](https://github.com/hydephp/framework/compare/v0.7.1-alpha...v0.7.2-alpha)

> 1 April 2022

- Create new command to scaffold pages [`#55`](https://github.com/hydephp/framework/pull/55)
- Create the action [`b788de2`](https://github.com/hydephp/framework/commit/b788de22a3175c3b09eadf15249d152026f0a160)
- Create the command [`eac5258`](https://github.com/hydephp/framework/commit/eac5258268a152496cf10bff05a23aa2977617eb)
- Clean up and format code [`dc5c5ee`](https://github.com/hydephp/framework/commit/dc5c5eef20df88b729bf749004001a0832d31302)

#### [v0.7.1-alpha](https://github.com/hydephp/framework/compare/v0.7.0-alpha...v0.7.1-alpha)

> 1 April 2022

- Add a favicon link automatically if the file exists [`#54`](https://github.com/hydephp/framework/pull/54)
- Create LICENSE.md [`57d4a1b`](https://github.com/hydephp/framework/commit/57d4a1b6122e7fcef021d84bff76a97b53424d0a)
- Use getPrettyVersion for composer version [`7569fb7`](https://github.com/hydephp/framework/commit/7569fb7616bcbaa22b30aad00bf559cb81578feb)
- Change version to the (pretty) framework version [`973cc74`](https://github.com/hydephp/framework/commit/973cc7414c8a398801e2cb52364f9eb44269cf3e)

#### [v0.7.0-alpha](https://github.com/hydephp/framework/compare/v0.6.2-alpha...v0.7.0-alpha)

> 1 April 2022

- Fix bug #47 StaticPageBuilder not able to create nested documentation directories [`#51`](https://github.com/hydephp/framework/pull/51)
- Remove _authors and _drafts directories #48 [`#49`](https://github.com/hydephp/framework/pull/49)
- Delete phpdoc.dist.xml [`b28afb7`](https://github.com/hydephp/framework/commit/b28afb712f7ea522e1fb9b2175223812d910b3a0)
- Remove _data directory [`a11ff92`](https://github.com/hydephp/framework/commit/a11ff9266ff3086c4e7a3ed17f7320e90cbd8788)
- Update author yml config path [`e0578bb`](https://github.com/hydephp/framework/commit/e0578bb8938c48b62540573fa88240932e629b4f)

#### [v0.6.2-alpha](https://github.com/hydephp/framework/compare/v0.6.1-alpha...v0.6.2-alpha)

> 30 March 2022

- Fix the documentation page header link [`#46`](https://github.com/hydephp/framework/pull/46)
- Use the indexpath basename for the doc header [`e188eb5`](https://github.com/hydephp/framework/commit/e188eb54f7d5c4fdc784fc16ffd7a60ad9ab458c)

#### [v0.6.1-alpha](https://github.com/hydephp/framework/compare/v0.6.0-alpha...v0.6.1-alpha)

> 30 March 2022

- Use relative path helper for links [`#45`](https://github.com/hydephp/framework/pull/45)
- Add support for nesting the documentation pages [`#42`](https://github.com/hydephp/framework/pull/42)

#### [v0.6.0-alpha](https://github.com/hydephp/framework/compare/v0.5.3-alpha...v0.6.0-alpha)

> 30 March 2022

- Fix the 404 route bug [`#41`](https://github.com/hydephp/framework/pull/41)
- #38 Add a rebuild command to the Hyde CLI to rebuild a specific file [`#39`](https://github.com/hydephp/framework/pull/39)
- Move scripts into app.js [`#35`](https://github.com/hydephp/framework/pull/35)
- #32 refactor command class names to be consistent [`#33`](https://github.com/hydephp/framework/pull/33)
- Add internal PHPDoc class descriptions [`#30`](https://github.com/hydephp/framework/pull/30)
- Require Torchlight [`#27`](https://github.com/hydephp/framework/pull/27)
- Restructure backend models [`#26`](https://github.com/hydephp/framework/pull/26)
- Rework how Markdown files are handled to improve maintainability and testing [`#25`](https://github.com/hydephp/framework/pull/25)
- 0.6.0 Remove support for Front Matter in Markdown Pages [`#24`](https://github.com/hydephp/framework/pull/24)
- Fix #21 by dynamically routing to the docs index [`#23`](https://github.com/hydephp/framework/pull/23)
- Merge pull request #23 from hydephp/21-bug-documentation-sidebar-header-should-link-to-readme-if-that-exists-but-an-index-does-not [`#21`](https://github.com/hydephp/framework/issues/21)
- Fix #21 by dynamically routing to the docs index [`#21`](https://github.com/hydephp/framework/issues/21)
- Add PHPUnit [`0d59ea0`](https://github.com/hydephp/framework/commit/0d59ea032a8b2be2f5c09db06563ab504e233d05)
- Create the HydeRebuildStaticSiteCommand [`92b1d20`](https://github.com/hydephp/framework/commit/92b1d20069482f851ee18629a0845a69e8f5a43a)
- Refactor to use the MarkdownFileService [`48a27a2`](https://github.com/hydephp/framework/commit/48a27a2799fd6a27e3bfa55417c2eb7fda3a4c43)

#### [v0.5.3-alpha](https://github.com/hydephp/framework/compare/v0.5.2-alpha...v0.5.3-alpha)

> 26 March 2022

- Remove deprecated methods [`#19`](https://github.com/hydephp/framework/pull/19)
- Make the command extend the base command [`eaba9da`](https://github.com/hydephp/framework/commit/eaba9dac5a9849804ccfdfc2798129fbe5cb0daf)
- Remove deprecated class [`24753c1`](https://github.com/hydephp/framework/commit/24753c1776c5f887baed82c93f02b632032ffde1)
- Format to PSR2 [`8307b65`](https://github.com/hydephp/framework/commit/8307b65087f73c3bbb40ecc7eb469db83c7777be)

#### [v0.5.2-alpha](https://github.com/hydephp/framework/compare/v0.5.1-alpha...v0.5.2-alpha)

> 25 March 2022

- Remove the Hyde installer [`#18`](https://github.com/hydephp/framework/pull/18)
- 0.6.x Remove deprecated command [`#17`](https://github.com/hydephp/framework/pull/17)
- Improve Docgen Feature by allowing the output directory to be dynamically changed [`#16`](https://github.com/hydephp/framework/pull/16)
- Rework installer prompts and fix wrong directory [`c15a4ac`](https://github.com/hydephp/framework/commit/c15a4acdf76e71221f3ba4c8d028ce2d0a7e3b0a)
- Allow the documentation output directory to be changed [`6cf07a3`](https://github.com/hydephp/framework/commit/6cf07a35aa3517d6691da3bb0ded266dea0e812a)
- Allow the homepage argument to be set from cli [`ab8dedd`](https://github.com/hydephp/framework/commit/ab8deddbebd73e458712cbde51a8c40a33fae38e)

#### [v0.5.1-alpha](https://github.com/hydephp/framework/compare/v0.5.0-alpha...v0.5.1-alpha)

> 24 March 2022

- Fix visual bug caused by setting max-width on body instead of article [`#15`](https://github.com/hydephp/framework/pull/15)
- Load commands in service provider instead of config/commands.php [`#13`](https://github.com/hydephp/framework/pull/13)
- Load commands in service provider instead of config [`46397fd`](https://github.com/hydephp/framework/commit/46397fd28e6cec25ec92ce44e047183b87346331)

#### [v0.5.0-alpha](https://github.com/hydephp/framework/compare/v0.4.3-alpha...v0.5.0-alpha)

> 24 March 2022

- Merge 0.5.0 into Master - Adds a multitude of new tests, code refactors and quality of life features [`#12`](https://github.com/hydephp/framework/pull/12)
- Sync branch with Master [`#11`](https://github.com/hydephp/framework/pull/11)
- Merge 0.5.x progress [`#10`](https://github.com/hydephp/framework/pull/10)
- Add _data directory and Authors object as well as stubs to aid in testing [`#9`](https://github.com/hydephp/framework/pull/9)
- Add required depedency to framework [`e5f0ec5`](https://github.com/hydephp/framework/commit/e5f0ec58df1163ef1de85a0b3233a347c45be136)
- Implement the Authors backend feature [`d7679f5`](https://github.com/hydephp/framework/commit/d7679f5b8d9ac900229a91d59099974cb82568e1)
- Add Commonmark as an explicit dependency [`bf915b1`](https://github.com/hydephp/framework/commit/bf915b130f418433ee2b47cc158229614883b090)

#### [v0.4.3-alpha](https://github.com/hydephp/framework/compare/v0.4.2-alpha...v0.4.3-alpha)

> 23 March 2022

- Add bindings for the package versions [`a9ce58d`](https://github.com/hydephp/framework/commit/a9ce58d2a9583866c05451ecf0da1dac4f84260b)
- Get version from facade [`465bafc`](https://github.com/hydephp/framework/commit/465bafc59fd0d20c5df91148d148d4c89a36e988)
- Replace Git version with Hyde version [`bcb7357`](https://github.com/hydephp/framework/commit/bcb7357f637138239bbee3ece007ff45564718bd)

#### [v0.4.2-alpha](https://github.com/hydephp/framework/compare/v0.4.1-alpha...v0.4.2-alpha)

> 23 March 2022

- v0.4.2-alpha Adds new meta tags and more data rich HTML [`#8`](https://github.com/hydephp/framework/pull/8)
- Add new meta tag options [`78a74c7`](https://github.com/hydephp/framework/commit/78a74c7c5342d6a8b528134022ba822e506cb12e)
- Add the Site URL feature, remember to update config! [`ee2f5c6`](https://github.com/hydephp/framework/commit/ee2f5c6b542ec3eb20412a8ef718b11cc1a9e23c)
- Add more rich HTML content [`8eb6778`](https://github.com/hydephp/framework/commit/8eb677849a655a30dffe5bfb3d48921ff4b24821)

#### [v0.4.1-alpha](https://github.com/hydephp/framework/compare/v0.4.0-alpha...v0.4.1-alpha)

> 22 March 2022

- Add the Hyde::getLatestPosts() shorthand to get the latest posts collection [`#4`](https://github.com/hydephp/framework/pull/4)
- Add new options to the build command to improve the user experience  [`#3`](https://github.com/hydephp/framework/pull/3)
- Remove progress bar from empty collections [`40d3203`](https://github.com/hydephp/framework/commit/40d3203d5494d37cea1b921f2a4447bc924d18d7)
- Add option to remove old files before building [`2650997`](https://github.com/hydephp/framework/commit/26509974c02a0c2d14f6fec490bdedc89a9b7725)
- Add options to automatically build frontend assets [`f789c2f`](https://github.com/hydephp/framework/commit/f789c2fc840e5bbffbf1df2b6a56576a846d48f5)

#### [v0.4.0-alpha](https://github.com/hydephp/framework/compare/v0.3.1-alpha...v0.4.0-alpha)

> 22 March 2022

- Add the console logo font [`2683a4b`](https://github.com/hydephp/framework/commit/2683a4b06d6ea646d2d3f6eaab32746df8a02da0)
- Add the config files [`47e9044`](https://github.com/hydephp/framework/commit/47e9044c3f63a02c8c5858d0a32861031126387c)
- Add the 404 page [`962cbe2`](https://github.com/hydephp/framework/commit/962cbe2886f2815a5c46de56b73e594cd3b12d1b)

#### [v0.3.1-alpha](https://github.com/hydephp/framework/compare/v0.3.0-alpha...v0.3.1-alpha)

> 22 March 2022

- Delete vendor directory [`4f96627`](https://github.com/hydephp/framework/commit/4f96627679a2e6de95520010a6f1bc98f30bca9f)
- 0.3.1 Move commands to framework [`70dd8df`](https://github.com/hydephp/framework/commit/70dd8df956e7fc1bc1c9b67a14e2b23a8fea4d76)
- Add php 8 require, and suggest hyde/hyde [`a8ff6ad`](https://github.com/hydephp/framework/commit/a8ff6ad9b3db7fe5bf69c638dd03b21309b85e42)

#### v0.3.0-alpha

> 21 March 2022

- Add the Core files (with temporary namespace) [`816ad3a`](https://github.com/hydephp/framework/commit/816ad3a24e5f95dff5aa1f1cfd581764fd1a1389)
- Initial Commit [`fa00787`](https://github.com/hydephp/framework/commit/fa007876e36ca6588147b05d44f927d7e8fbf997)
- Successfully move namespace Core to Framework [`0c9160f`](https://github.com/hydephp/framework/commit/0c9160f33124701e6ed21a1e5b2bd70f46aaa65a)
