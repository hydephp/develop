# HydePHP Frontend Assets
![jsDelivr hits (GitHub)](https://img.shields.io/jsdelivr/gh/hm/hydephp/hydefront)
[![jsDelivr hits (NPM)](https://data.jsdelivr.com/v1/package/npm/hydefront/badge?style=rounded)](https://www.jsdelivr.com/package/npm/hydefront)
![NPM Downloads](https://img.shields.io/npm/dm/hydefront)
[![Build & Push CI](https://github.com/hydephp/hydefront/actions/workflows/node.js.yml/badge.svg)](https://github.com/hydephp/hydefront/actions/workflows/node.js.yml)
[![CodeQL](https://github.com/hydephp/hydefront/actions/workflows/codeql.yml/badge.svg)](https://github.com/hydephp/hydefront/actions/workflows/codeql.yml)

## About this repository

Contains the frontend assets for HydePHP stored in hydephp/framework under resources/assets. See https://github.com/hydephp/framework/tree/master/resources/assets

### Source files
Source files are stored in the root of the repository. These can be published to your Hyde installation or used to compile into the dist/ directory.

### Compiled files
Compiled files are stored in the dist/ directory and can be loaded through the CDN or NPM.

They are included in the Hyde/Framework package and can be used locally by customizing the Blade view.

### About the files

- **App.css**:
A compiled and minified file containing the styles for a base Hyde installation.

- **Hyde.css**:
The Hyde stylesheet contains the custom base styles and should be loaded after App.css as it contains some Tailwind tweaks.

- **Hyde.js**:
This file contains basic scripts to make the navigation menu and sidebars interactive.

## Usage
Note that HydeFront is included in Hyde/Hyde out of the box.

### Using CDN
See https://www.jsdelivr.com/package/npm/hydefront

```html
<link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/hydephp/hydefront@v1.3/dist/app.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/hydephp/hydefront@v1.3/dist/hyde.css">

<script defer src="https://cdn.jsdelivr.net/gh/hydephp/hydefront@v1.3/dist/hyde.js"></script>
```

### Using NPM
See https://www.npmjs.com/package/hydefront

## Links:
- GitHub https://github.com/hydephp/hydefront
- NPM https://www.npmjs.com/package/hydefront
- jsDelivr https://www.jsdelivr.com/package/npm/hydefront

## Beta software notice
HydePHP is currently in beta. Please report any bugs and issues in the appropriate issue tracker. Versions in the 0.x series are not stable and may change at any time. No backwards compatibility guarantees are made and breaking changes are <s>possible</s> <i>expected</i>.
