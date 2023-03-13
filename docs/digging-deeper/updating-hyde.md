---
navigation:
    priority: 35
---

# Updating Hyde Projects

This guide will help you update your HydePHP project to the latest version.


## Before you start

When updating an existing installation, first ensure that you have a backup of your project in case anything goes wrong.
The recommended way to do this is to use Git as that allows you to smoothly roll back any changes.


## Methods

### Which method?

Depending on how you installed Hyde, there are a few different ways to update it.

We have three methods documented here, one [using Git](#using-git) and two [manual options](#manual-update).

The Git method works great, especially if you created your project by cloning the [hydephp/hyde](https://github.com/hydephp/hyde)
repository, though you can use it with any project.

Regardless of the method you use, make sure you follow the [post-update instructions](#post-update-instructions) at the end.

### Using Git

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

Next, follow the post-update instructions.

### Manual Update

If you are not using Git, you can still update your project. This is a bit more involved, but it is still possible.

1. First, you will need to download the latest release archive from the [releases page](https://github.com/hydephp/hyde/releases).
2. Then extract the archive, and copy the contents into your project directory.

Since this may overwrite modified files, it may be safer to use the [hard update](#hard-update) method.

### Hard Update

If you are having trouble updating your project, you can try a hard update. In short, this approach consists of creating
a brand new project and copying over only your source and resource files. If you do not want to use Git, this may be
the safest option as you won't be overriding any of your existing files.

If you have changed any other files, for example in the App directory, you will need to update those files manually as well.
The same goes if you have created any custom Blade components or have modified Hyde ones.

**Here is an example CLI workflow, but you can do the same using a graphical file manager.**

```bash
mv my-project my-project-old
composer create-project hyde/hyde my-project

cp -r my-old-project/_pages my-project/content/_pages
cp -r my-old-project/_posts my-project/content/_posts
cp -r my-old-project/_media my-project/content/_media
cp -r my-old-project/_docs my-project/content/_docs
cp -r my-old-project/config my-project/config
```

Next, follow the post-update instructions. After verifying that everything is working, you can delete the old project directory.


## Post-update instructions

After updating Hyde you should update your config and resource files. This is where things can get a tiny bit dangerous
as existing files may be overwritten. If you are using Git, you can easily take care of any merge conflicts that arise.

First, ensure that your dependencies are up to date. If you have already done this, you can skip this step.

```bash
composer update
```

Then, update your config files. This is the hardest part, as you may need to manually copy in your own changes.

```bash
php hyde publish:configs
```

If you have published any of the included Blade components you will need to re-publish them.

```bash
php hyde publish:views layouts
php hyde publish:views components
```

Next, recompile your assets, if you are not using the built-in assets.

```bash
npm install
npm run dev/prod
```

Finally, you can rebuild your site.

```bash
php hyde build
```
