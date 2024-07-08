# Internal HydeFront documentation

## Building and creating a new HydeFront version

### Prerequisites

- Make sure you have a Git submodule set up in `packages/hydefront` that points to the HydeFront repository.
- Make sure you have authorization to publish the package to NPM and push to the HydeFront repository.

### Build and setup

```bash
cd packages/hydefront
git pull origin master
npm run prod
```

### Versioning

Head back to the monorepo root and run the following command to bump the version of the HydeFront package:

```bash
php packages/hydefront/.github/scripts/version.php patch|minor|major
```

This will create commits in both the monorepo and submodule. Now follow the following steps:

1. Verify that both commits are correct.
2. Push the submodule commit to the HydeFront repository.
3. Create the release on GitHub. Make sure to use the same version number as the one you just bumped.
4. Refetch the submodule origin to get the new tag created by the release. 
5. Publish the package to NPM. (In the future, this could be automated with a GitHub action from the release.)

### Updating the monorepo

After the HydeFront package has been published, you can update the monorepo to use the new version. Run the following command:

```bash
npm update hydefront
```

Now, you may want to amend the monorepo commit with the updated lock file, then it can be pushed to the monorepo repository.

