# Projects in this monorepo

The following directories are self contained packages:

- framework: Hyde/Framework
- hydefront: Hyde/HydeFront
- realtime-compiler: Hyde/RealtimeCompiler
- testing: Hyde/Testing
- ui-kit: Hyde/UI-Kit

The Hyde/Hyde package is the root directory of the monorepo, however, some persisted data is stored in here and is merged when splitting the monorepo. This means that the Hyde directory here is not a complete package, but rather a container for the persisted data.
