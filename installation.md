# Installation Guide

## Installing HydePHP using Composer (recommended)
The recommended method of installing Hyde is using Composer. After the project has been created you can scaffold a new blog post using the `make` command and following the on-screen instructions, and then compile the site into static HTML using the `build` command.

```bash
// torchlight! {"lineNumbers": false}
composer create-project hyde/hyde example-site

cd example-site

npm install && npm run dev

php hyde make:post

php hyde build
```

If you now take a look in the `_site` directory you should see that an index.html file, as well as a posts/hello-world.html file, has been created! Open them up in your browser and take a look!

> If you are missing the stylesheet, run `npm install && npm run dev`


## Installing HydePHP Git/GitHub

If you want to run the latest development branch of Hyde (not recommended for production!) you can install it directly from Git/GitHub.

### Clone the repo
There are two methods for creating a new project using Git/GitHub.
The first one is using the GitHub website where you can clone the template repository using the green button labelled "Use this template" found at https://github.com/hydephp/Hyde.

Or if you want to use the CLI, run
```bash
// torchlight! {"lineNumbers": false}
git clone https://github.com/hydephp/hyde.git
```

### Finalizing
Next, navigate into the created project and install the dependencies and build the assets.
```bash
// torchlight! {"lineNumbers": false}
cd hyde
composer install
npm install
npm run dev
```


### Usage
After the project has been created you can scaffold a new blog post using the `make` command and following the on-screen instructions, and then compile the site into static HTML using the `build` command.

```bash
// torchlight! {"lineNumbers": false}
php hyde make:post

php hyde build
```

If you now take a look in the `_site` directory you should see that an index.html file, as well as a posts/hello-world.html file, has been created! Open them up in your browser and take a look!

## Next steps

Make sure you check out the [getting started](getting-started.html) page to learn how to use Hyde!
