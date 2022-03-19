---
title: Creating a static HTML post using HydePHP
description: In this tutorial, we go through the simple process of generating a static blog post
category: tutorials
author: Caen
date: 2022-03-19 16:19
---

> This tutorial assumes you have already setup HydePHP.

We will be using the make command to scaffold our file. In your favourite terminal, navigate to your project directory and run the command.

```bash
php hyde make:post
```

We should now get the following output:
```bash
Creating a new post!

Please enter the title of the post, it will be used to generate the slug.

What is the title of the post?:
>
```

Let's fill in the title we want and hit enter.

Next, we will be asked to fill in some meta information. These are not required and you can just hit return to use the defaults, though they will make the post look nice so we will add them here!

```bash
What is the title of the post?:
 > Creating a static HTML post using HydePHP

Tip: You can just hit enter to use the defaults.

Write a short post excerpt/description:
 > In this tutorial we go through the simple process of generating a static blog post

What is your (the authors) name?:
 > Caen

What is the primary category of the post?:
 > tutorials
```

Next, we will be given a preview of what the post will look like. If something does not look write we can write `no` to abort. But for now, we will hit enter to use the preselected `yes` option.

```bash
Creating a post with the following details:
Title: Creating a static HTML post using HydePHP
Description: In this tutorial, we go through the simple process of generating a static blog post
Author: Caen
Category: tutorials
Date: 2022-03-19 16:19
Slug: creating-a-static-html-post-using-hydephp

Do you wish to continue? (yes/no) [yes]:
 > yes

Post created! File is saved to /dev/HydeDocs/_posts/creating-a-static-html-post-using-hydephp.md
```

Awesome! As you can see the current date has automatically been injected using the proper format. A slug has also been generated.

We can also use the outputted file path to open the Markdown file in our text editor. I'm using VSCode.

This is the contents of the file. The title has also been filled in for us.
```markdown
---
title: Creating a static HTML post using HydePHP
description: In this tutorial, we go through the simple process of generating a static blog post
category: tutorials
author: Caen
date: 2022-03-19 16:19
slug: creating-a-static-html-post-using-hydephp
---

# Creating a static HTML post using HydePHP
```

Now that we have the file, let's fill in the post with actual content and then we can build the site!

If this is the first time you are building the site you may need to compile the frontend assets using NPM. If you don't have NPM you can download the files from the latest GitHub release and add them to the `_site` directory.

```bash
npm install
npm run dev
```

And then we build the site with
```bash
php hyde build
```

We can now open up the created file with is saved in `_site/posts/creating-a-static-html-post-using-hydephp.html`!
