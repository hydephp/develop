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
