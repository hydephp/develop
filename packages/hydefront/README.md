# HydePHP Frontend Assets
![jsDelivr hits (GitHub)](https://img.shields.io/jsdelivr/gh/hm/hydephp/hydefront)
[![jsDelivr hits (NPM)](https://data.jsdelivr.com/v1/package/npm/hydefront/badge?style=rounded)](https://www.jsdelivr.com/package/npm/hydefront)
![NPM Downloads](https://img.shields.io/npm/dm/hydefront)
[![Build & Push CI](https://github.com/hydephp/hydefront/actions/workflows/node.js.yml/badge.svg)](https://github.com/hydephp/hydefront/actions/workflows/node.js.yml)
[![CodeQL](https://github.com/hydephp/hydefront/actions/workflows/codeql.yml/badge.svg)](https://github.com/hydephp/hydefront/actions/workflows/codeql.yml)

## About this repository

Contains the frontend assets for HydePHP.

### Source files
Source files are stored in the root of the repository. These can be published to your Hyde installation or used to compile into the dist/ directory.

### Compiled files
Compiled files are stored in the dist/ directory and can be loaded through the CDN or NPM.

### About the files

- **Hyde.css**:
The Hyde stylesheet contains the base styles for the Hyde views.

- **Hyde.js**:
This file contains basic scripts to make the navigation menu and sidebars interactive.

- **App.css**:
A compiled and minified file containing the styles for a base Hyde installation using [this configuration file](https://github.com/hydephp/hyde/blob/master/tailwind.config.js).


## Usage
Note that HydeFront is included in Hyde/Hyde through the CDN out of the box.

### Using CDN
See https://www.jsdelivr.com/package/npm/hydefront

```html
<link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/hydephp/hydefront@v1.8/dist/hyde.css">

<script defer src="https://cdn.jsdelivr.net/gh/hydephp/hydefront@v1.8/dist/hyde.js"></script>
```

### Using NPM (with Laravel Mix)
HydeFront is also available as an [NPM package](https://www.npmjs.com/package/hydefront), if you want to compile all your assets using Laravel Mix. Note that it is recommended to use the CDN as the Framework takes care of versioning.

Install the package
```bash
npm install hydefront
```

Next, add the following import to `resources/assets/app.css`
```css
@import '~hydefront/dist/hyde.css';
```

Then, publish the `styles.blade.php` view and replace the CDN stylesheet with the path to your compiled file.
> Note, if you are on Hyde v0.41.x or newer, the Hyde CDN is automatically disabled and will load the compiled file if the `_media/hyde.css` file exists, same for the `_media/hyde.js` file (independent of each other).

And compile your assets
```bash
npm run dev/prod
```

## Links:
- GitHub https://github.com/hydephp/hydefront
- NPM https://www.npmjs.com/package/hydefront
- jsDelivr https://www.jsdelivr.com/package/npm/hydefront

## Warranty:
HydeFront is not intended to be used for standalone projects and comes with no warranties. Changes in HydeFront are tied to those in the Hyde Framework and differing versions may be incompatible.

## Beta software notice
HydePHP is currently in beta. Please report any bugs and issues in the appropriate issue tracker. Versions in the 0.x series are not stable and may change at any time. No backwards compatibility guarantees are made and breaking changes are <s>possible</s> <i>expected</i>.
