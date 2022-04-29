# HydePHP Frontend Assets
[![](https://data.jsdelivr.com/v1/package/npm/hydefront/badge)](https://www.jsdelivr.com/package/npm/hydefront)
![jsDelivr hits (GitHub)](https://img.shields.io/jsdelivr/gh/hm/hydephp/hydefront)
[![Build & Push CI](https://github.com/hydephp/hydefront/actions/workflows/node.js.yml/badge.svg)](https://github.com/hydephp/hydefront/actions/workflows/node.js.yml)
[![CodeQL](https://github.com/hydephp/hydefront/actions/workflows/codeql.yml/badge.svg)](https://github.com/hydephp/hydefront/actions/workflows/codeql.yml)

## About HydePHP
HydePHP is a Static App Generator powered by Laravel Zero, allowing for the rapid creation of beautiful, responsive, and customizable websites.
See https://hydephp.github.io/ for more!

## About this repository

Contains the frontend assets for HydePHP stored in hydephp/framework under resources/assets.

### Source files
Source files are stored in the root of the repository. These can be published to your Hyde installation, or used to compile into the dist/ directory.

### Compiled files
Compiled files are stored in the dist/ directory and can be loaded through the CDN.

They are included in the Hyde/Framework package and can be used locally by customizing the Blade view.

### About the files

- **App.css**:
A compiled and minified file containing the styles for a base Hyde installation.

- **Hyde.css**:
The Hyde stylesheet contains the custom base styles and should be loaded after App.css as it contains some Tailwind tweaks.

- **Hyde.js**:
This file contains basic scripts to make the navigation menu and sidebars interactive.

## Usage

### Using CDN
```html
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/hydefront@v1.3/dist/app.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/hydefront@v1.3/dist/hyde.css">

<script defer src="https://cdn.jsdelivr.net/npm/hydefront@v1.3/dist/hyde.js"></script>
```

## Links:
- GitHub https://github.com/hydephp/hydefront
- NPM https://www.npmjs.com/package/hydefront
- jsDelivr https://www.jsdelivr.com/package/npm/hydefront

## Beta software notice
HydePHP is a currently in beta. Please report any bugs and issues in the appropriate issue tracker. Versions in the 0.x series are not stable and may change at any time. No backwards compatibility guarantees are made and breaking changes are <s>possible</s> <i>expected</i>.
