# About this directory

Files in this directory are the source of truth for the Hyde frontend resources.

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
