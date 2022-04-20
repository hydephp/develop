# HydePHP Frontend Assets
![jsDelivr hits (GitHub)](https://img.shields.io/jsdelivr/gh/hm/hydephp/hydefront)

## About this repository

Contains the frontend assets for HydePHP stored in hydephp/framework under resources/frontend.

### Source files
Source files are stored in the root of the repository. These can be published to your Hyde installation, or used to compile into the dist/ directory.
- hyde.scss (Sass) - Compiled to hyde.css and hyde.min.css
- hyde.js (JavaScript) - Compiled to hyde.js and hyde.min.js
- app.css (Tailwind source) - Starter CSS for a Hyde installation

### Compiled files
Compiled files are stored in the dist/ directory and can be loaded through a CDN.
- hyde.css, hyde.min.css
- hyde.js, hyde.min.js
- tailwind.min.css

### About the files

#### App.css
This file is mostly blank and only contains the TailwindCSS imports and is the suggested location for users to place their own custom CSS unless they add a custom.css file which in that case should be loaded after all the others.

#### Hyde.css/Hyde.scss
The Hyde stylesheet contains the custom base styles and should be loaded after App.css as it contains some Tailwind tweaks.

#### Hyde.js
This file contains basic scripts to make the navigation menu and sidebars interactive.

#### Tailwind.css
A compiled and minified file containing the styles for a base Hyde installation.

## Usage

The frontend files are stored in the Hydephp/Framework repo in the `resources/frontend` directory and are by default loaded into Hyde installations and can be republished using the following command:

```bash
php hyde update:resources
```

## Beta software notice
HydePHP is a currently in beta. Please report any bugs and issues in the appropriate issue tracker. Versions in the 0.x series are not stable and may change at any time. No backwards compatibility guarantees are made and breaking changes are <s>possible</s> <i>expected</i>.
