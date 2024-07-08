# Internal HydeFront documentation

## Building and creating a new HydeFront version

```bash
cd packages/hydefront
npm run prod

cd ../../
php packages/hydefront/.github/scripts/version.php patch|minor|major
```
