---
title: Why static sites?
description: A quick run-down of the benefits of static websites
category: general
author: Caen
date: 2022-03-19 20:02
---

<p class="lead">
At this point, you may be wondering: "Why use static sites?" <br>
Here is a rundown of my top reasons to go static.
</p>

## Speed, scalability, simplicity

With a static site, you don't need to worry about setting up databases. This makes the site so much faster as you don't need to wait for a database to process requests. By pre-compiling the sites you also don't need to waste time and processing power on server-side rendering which also speeds up your site. Furthermore, it makes your site incredibly scalable as you don't need to worry about keeping replica databases in sync.

You can even serve the site from global CDNs on the Edge for amazing speed.


## Security, stability, and cost
You don't need to worry about keeping your database secure since there is no database. You can also rest easy knowing your site is stable and that you don't need to maintain a complex backend. You can also rest assured that there won't be any unexpected runtime errors that are hard to find.

You can also use create a Git powered CMS to collaborate on Markdown posts.

Static web hosting has become incredibly cheap, to the point where dozens of companies offer free hosting.

## Conclusion
Are you ready to go static? Why not give HydePHP a spin! That's what this blog uses. It's stupidly simple, endlessly hackable, and in my opinion: totally awesome.

-> Dive in to the source code at [GitHub](https://github.com/hydephp/hyde)
-> Check out the [documentation](docs/index.html) (built with Hyde of course) 

You can also create a new project using Composer:
```bash
composer create-project hyde/hyde
```
