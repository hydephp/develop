# Hyde Releases

While in the v0.x range, we consider both major and minor release versions to be the same. This means that when a new feature is added, it should be added as a minor version even if it is breaking. Patch versions should always be compatible. Once we reach v1.0 we will follow semantic versioning strictly.

The versioning between the Framework and Hyde packages are linked together Meaning that if Hyde get's a minor release, so must Framework, and vice versa. To make this easier, we also publish minor releases in the monorepo. Patch releases are not published in the monorepo, and are instead handled by the individual packages.

To make this all easier, this directory contains tools to automate part of the process.
