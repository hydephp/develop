---
navigation:
    priority: 35
---

# Updating Hyde Projects

This guide will help you update your HydePHP project to the latest version.

### Before you start

When updating an existing installation, first ensure that you have a backup of your project in case anything goes wrong.
The recommended way to do this is to use Git as that allows you to smoothly roll back any changes.

#### Which method?

Depending on how you installed Hyde, there are a few different ways to update it.

We have two methods documented here, one [using Git](#using-git) and one [manually](#manual-update).

Regardless of the method you use, make sure you follow the post-update instructions at the end.

### Using Git

This method works great, especially if you created your project by cloning the [hydephp/hyde](https://github.com/hydephp/hyde) repository.

First, make sure you have a remote set up for the base project repository.

```bash
git remote add upstream https://github.com/hydephp/hyde.git
```

Then pull the latest release from the upstream repository.

```bash
git pull upstream master
```

After this, you should update your composer dependencies:

```bash
composer update
```

Next, follow the post-update instructions for Hyde/Hyde.

### Manual Update

Since all resource files are in the content directories you can simply copy those files to the new location.

If you have changed any other files, for example in the App directory, you will need to update those files manually as well.
But if you have done that you probably know what you are doing. I hope. The same goes if you have created any custom blade components or have modified Hyde ones.

Example CLI workflow, assuming the Hyde/Hyde project is stored as `my-project` in the home directory:

```bash
cd ~
mv my-project my-project-old
composer create-project hyde/hyde my-project

cp -r my-old-project/_pages my-project/content/_pages
cp -r my-old-project/_posts my-project/content/_posts
cp -r my-old-project/_media my-project/content/_media
cp -r my-old-project/_docs my-project/content/_docs
cp -r my-old-project/config my-project/config
```

Next, follow the post-update instructions for Hyde/Hyde. After verifying that everything is working, you can delete the old project directory.


## Post-update instructions

After updating Hyde you should update your config and resource files. This is where things can get a tiny bit dangerous as the files will be overwritten. However, since you should be using Git, you can take care of any merge conflicts that arise.

```bash
php hyde publish:configs
php hyde update:assets
```

If you have published any of the Hyde Blade components you will need to re-publish them.

```bash
php hyde publish:views layouts
php hyde publish:views components
```

Next, re-build your site.

```bash
php hyde build
```

And recompile your assets if applicable.

```bash
npm install
npm run dev/prod
```
