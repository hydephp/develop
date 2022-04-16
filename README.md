# Experimental Hyde Realtime Server

## ⚠ Important! This feature is experimental at best and is in no way intended to be used in production.
Though, why you would want a dynamic web server to serve static HTML I don't know anyway.

So again, this is just a local testing tool and comes with no warranty in any way whatsoever.

### Introduction video
[<img src="https://user-images.githubusercontent.com/95144705/163690301-393380f3-4e3f-4ead-a78e-cb79ab20eadc.png" title="Watch on YouTube" alt="YouTube Thumbnail" width="600px"></img>](https://www.youtube.com/watch?v=1ZM4fQMKi64)

## Installation

## Install with Composer

```
composer require hyde/realtime-compiler
```

## Manual / Git install

Clone the contents of the repo into `<hyde-project>/extensions/realtime-compiler`.

## Usage

### Running through Composer
> Currently running the bin file through the composer bin directory will not work as it is not compatible with the built in web server. See https://githubhot.com/repo/composer/composer/issues/10533 for more information.

Instead run the following command:

```bash
php -S localhost:80 ./vendor/hyde/realtime-compiler/server.php
```


### Running from the extension path
> Note, the paths in this section are relative to the package root and assuming the source is in the extensions/realtime-compiler directory.

To start a preconfigured server with the default settings, run:

Unix:

```bash
$ bash ./bin/serve.sh
```

Windows:
```powershell
PS: .\bin\serve.bat
```

Or if you want to start the server with a custom port:

```bash
php -S localhost:80 server.php
```

### A note on media files
Currently the RC only proxies media files, it does not compile them. It first attempts to find the requested file in _site/media, then in _media. If it is not found a 404 response is returned. To watch media files for changes, for example for compiling Tailwind CSS, you can use any of the existing NPM commands.

## Tests
You may have noticed there are no tests for this extension. The reason for this is mainly because the extension is so lightweight and I found it hard to write tests without the many mocking libraries that are usually used.

As it is now, there are zero dependencies, and adding a test suite would add a lot of overhead. However, if you are interested in contributing in tests in a way that works, please do so! I am honestly not skilled enough with PHPUnit to do it.
