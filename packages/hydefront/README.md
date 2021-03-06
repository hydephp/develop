# HydePHP Frontend Assets

[![jsDelivr hits (NPM)](https://data.jsdelivr.com/v1/package/npm/hydefront/badge?style=rounded)](https://www.jsdelivr.com/package/npm/hydefront)
![NPM Downloads](https://img.shields.io/npm/dm/hydefront)
[![Build & Push CI](https://github.com/hydephp/hydefront/actions/workflows/node.js.yml/badge.svg)](https://github.com/hydephp/hydefront/actions/workflows/node.js.yml)

## About this repository

Contains the frontend assets for HydePHP.

### About the files

- **Hyde.css**:
The Hyde stylesheet contains a small set of styles that don't make sense to use with Tailwind, or that belong to generated content that is harder to modify.

Normally, this file is imported into the main.css file.

- **App.css**:
A compiled and minified file containing the TailwindCSS styles for a base Hyde installation. It includes the Hyde.css file. This file is identical to what one would get by running `npm run prod` in a new HydePHP project (which incidentally is exactly how this file is generated).

## Usage

HydeFront is included with [HydePHP](https://github.com/hydephp/hyde) by default.

## Links:
- GitHub https://github.com/hydephp/hydefront
- NPM https://www.npmjs.com/package/hydefront
- jsDelivr https://www.jsdelivr.com/package/npm/hydefront

## Warranty:
HydeFront is not intended to be used for standalone projects and comes with no warranties. Changes in HydeFront are tied to those in the Hyde Framework and differing versions may be incompatible.

## Beta software notice
HydePHP is currently in beta. Please report any bugs and issues in the appropriate issue tracker. Versions in the 0.x series are not stable and may change at any time. No backwards compatibility guarantees are made and breaking changes are <s>possible</s> <i>expected</i>.

## v2.0 Release Notes

HydeFront version 2.0 is a major release and has several breaking changes.
It is not compatible with HydePHP versions lower than v0.50.0-beta.

Many files have been removed, as HydePHP now uses Alpine.js for interactions, and TailwindCSS for the documentation pages.

HydeFront v1.x will receive security fixes only.
