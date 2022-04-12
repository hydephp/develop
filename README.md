# HydePHP Frontend Assets

## About this repository

Files in this repository are the source of truth for the Hyde frontend resources.

### Source files
- hyde.scss
- hyde.js

### Compiled files
- hyde.css
- hyde.min.js


### About the files

#### App.css
This file is mostly blank and only contains the TailwindCSS imports and is the suggested location for users to place their own custom CSS.

#### Hyde.css/Hyde.scss
The Hyde stylesheet contains the base styles and should be loaded after App.css as it contains some Tailwind tweaks.

The hyde.css file is the compiled and minified version of the hyde.scss file.

Compile it using the following command: (Assuming Dart Sass)

```bash
sass hyde.scss hyde.css --style=compressed --no-source-map
```

#### Hyde.js
This file contains basic scripts to make the navigation menu and sidebars interactive.

## Usage

The frontend files are stored in the Hydephp/Framework repo in the `resources/frontend` directory and are by default loaded into Hyde installations and can be republished using the following command:

```bash
php hyde update:resources
```

## Beta software notice
HydePHP is a currently in beta. Please report any bugs and issues in the appropriate issue tracker. Versions in the 0.x series are not stable and may change at any time. No backwards compatibility guarantees are made and breaking changes are <s>possible</s> <i>expected</i>.
