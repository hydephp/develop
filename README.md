# HydePHP Frontend Assets
![jsDelivr hits (GitHub)](https://img.shields.io/jsdelivr/gh/hm/hydephp/hydefront)

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
<link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/hydephp/hydefront@v1.3.1/dist/app.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/hydephp/hydefront@v1.3.1/dist/hyde.css">

<script defer src="https://cdn.jsdelivr.net/gh/hydephp/hydefront@v1.3.1/dist/hyde.js"></script>
```


## Beta software notice
HydePHP is a currently in beta. Please report any bugs and issues in the appropriate issue tracker. Versions in the 0.x series are not stable and may change at any time. No backwards compatibility guarantees are made and breaking changes are <s>possible</s> <i>expected</i>.
