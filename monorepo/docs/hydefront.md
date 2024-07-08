# Internal HydeFront documentation

## Building and creating a new HydeFront version

Prerequisites:

- Make sure you have a Git submodule set up in `packages/hydefront` that points to the HydeFront repository.
- Make sure you have authorization to publish the package to NPM and push to the HydeFront repository.

```bash
cd packages/hydefront
git pull origin master
npm run prod

cd ../../
php packages/hydefront/.github/scripts/version.php patch|minor|major
```
