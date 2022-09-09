# Hyde Realtime Compiler Server

The source code for the HydePHP Realtime Compiler/Server. This package is included with HydePHP through hyde/hyde.

The package adds a `php hyde serve` command which exposes a web server on port `8080` which will compile the requested web page on the fly and serve it to the browser.

## Supported Versions

The 2.0 release brings a total rewrite. See the release notes below.

The upgrade will be handled within Hyde/Framework and standard usage will not be affected.
Any third-party integrations will need to be handled manually. v1.0x will receive security fixes for the time being.
See the [Security Policy](https://github.com/hydephp/realtime-compiler/security/policy).


| Version | Supported          | Notes  |
|---------|--------------------|--------|
| 2.x     | :white_check_mark: | Latest |
| 1.x     | :shield:           | (LTS*) |
| < 1.0   | :x:                | Alpha  |

*1.x LTS receives security fixes only

## v2.0 Release Notes

### Preface

This release completely rewrites the Hyde Realtime Compiler.

The application server is now powered by the lightweight [Microserve](https://github.com/caendesilva/microserve)
HTTP server API, providing a robust and fast way to route and handle requests without any extra dependencies.

In addition, the source files are no longer compiled by calling the HydeCLI in a separate process,
instead, the server compiles source files directly through the Hyde Framework code without any
intermediary process.

This gives the huge benefit of being able to catch errors and exceptions directly,
without having to search for them in the console output like in v1.x.

### Internal Changes

- HTTP logic is handled by [Microserve](https://github.com/caendesilva/microserve), through the new [HttpKernel](https://github.com/hydephp/realtime-compiler/blob/master/src/Http/HttpKernel.php).
- Requests are sent to the Router to be processed by the appropriate handler.
- Web pages are compiled through the [Hyde Framework](https://github.com/hydephp/framework), instead of calling the HydeCLI.
- Static assets are proxied directly without booting the entire framework.
- Exceptions are handled by [Whoops](https://github.com/filp/whoops), through the ExceptionHandler.
- The server.php is moved to the `bin` directory.

### API Changes

The way the server works for the end-user has not been changed much.
Most people will not even notice anything but the new features.

The only real change is that applications will need to point to the
new `server.php` file which is now located in the `bin` directory.

The debug/console output has been removed.
