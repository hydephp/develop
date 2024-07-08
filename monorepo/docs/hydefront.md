# Internal HydeFront documentation

## Building and creating a new HydeFront version

### Prerequisites

- Make sure you have a Git submodule set up in `packages/hydefront` that points to the HydeFront repository.
- Make sure you have authorization to publish the package to NPM and push to the HydeFront repository.
- **Make sure that you have linked the local HydeFront package to the monorepo with `npm link`.**

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
2. Build the new `app.css` through the monorepo
    - [ ] `npm run prod`
3. Copy the compiled file to the HydeFront repository so it can be served from the CDN.
    - [ ] `cp _media/app.css packages/hydefront/dist/app.css`
4. Amend the HydeFront commit with the new `app.css` file.
    - [ ] `cd packages/hydefront && git add dist/app.css && git commit --amend --no-edit`
5. Push the submodule commit to the HydeFront repository.
    - [ ] `cd packages/hydefront && git push origin master`
6. Create the release on GitHub. Make sure to use the same version number as the one you just bumped.
    - [ ] Open https://github.com/hydephp/hydefront/releases/new
7. Refetch the submodule origin to get the new tag created by the release.
    - [ ] `cd packages/hydefront && git fetch origin`
8. Publish the package to NPM. (In the future, this could be automated with a GitHub action from the release.)
   - [ ] `npm publish`

### Updating the monorepo

After the HydeFront package has been published, you can update the monorepo to use the new version. Run the following command:

```bash
npm update hydefront
```

Now, you may want to amend the monorepo commit with the updated lock file, then it can be pushed to the monorepo repository.

