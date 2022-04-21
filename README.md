# HydePHP Frontend Assets
![jsDelivr hits (GitHub)](https://img.shields.io/jsdelivr/gh/hm/hydephp/hydefront)

## About this repository

Contains the frontend assets for HydePHP stored in hydephp/framework under resources/frontend.

### Source files
Source files are stored in the root of the repository. These can be published to your Hyde installation, or used to compile into the dist/ directory.

### Compiled files
Compiled files are stored in the dist/ directory and can be loaded through a CDN. They are included in the Hyde/Framework package, and can be re-published using the hyde command `php hyde update:resources`.

### About the files

- **App.css**:
This file is mostly blank and only contains the TailwindCSS imports and is the suggested location for users to place their own custom CSS unless they add a custom.css file which in that case should be loaded after all the others.

- **Hyde.scss**:
The Hyde stylesheet contains the custom base styles and should be loaded after App.css as it contains some Tailwind tweaks.

- **Hyde.js**:
This file contains basic scripts to make the navigation menu and sidebars interactive.

- **Tailwind.css**:
A compiled and minified file containing the styles for a base Hyde installation.

## Usage

### Using CDN
```html
<link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/hydephp/hydefront@v1.2.0/dist/hyde.min.css">

<script defer src="https://cdn.jsdelivr.net/gh/hydephp/hydefront@v1.2.0/dist/hyde.min.js"></script>
```

### Updating Framework assets
The frontend files are stored in the Hydephp/Framework repo in the `resources/frontend` directory and are by default loaded into Hyde installations and can be republished using the following command:

```bash
php hyde update:resources
```

## Beta software notice
HydePHP is a currently in beta. Please report any bugs and issues in the appropriate issue tracker. Versions in the 0.x series are not stable and may change at any time. No backwards compatibility guarantees are made and breaking changes are <s>possible</s> <i>expected</i>.
