---
navigation:
    priority: 25
    label: "Compile & Deploy"
---

# Compile and Deploy Your Site

## Running the Build Command

Now that you have some amazing content, you'll want to compile your site into static HTML.

**This is as easy as executing the `build` command:**

```bash
php hyde build
```

**You can also compile a single file:**

```bash
php hyde rebuild <filepath>
```

**And, you can even start a development server to compile your site on the fly:**

```bash
php hyde serve
```

### Further reading

>info <p><b>Key Concept: Autodiscovery</b></p> <p>When building the site, Hyde will use all the routes generated when the auto-discovery process scanned your source directories for files. The command will then compile them into static HTML using the appropriate layout depending on what kind of page it is. Thanks to Hyde, the days of manually defining routes are over!</p>

#### Learn more about these commands in the [console commands](console-commands) documentation:

- [Build command](console-commands#build-the-static-site)
- [Rebuild command](console-commands#build-a-single-file)
- [Serve command](console-commands#start-the-realtime-compiler)

---

## Deploying Your Site

One of the things that make static sites so enjoyable to work with is how easy it is to deploy them to the web.
This list is not exhaustive, but gives you a general idea of the most common ways to deploy your site.
If you have ideas to add to the documentation, please send a pull request!

### General deployment

In essence, all you need to do is copy the contents of the `_site` directory to a web server, and you're done.

Once the site is compiled there is nothing to configure or worry about.

### FTP and File Managers

If you have a conventional web host, you can use `FTP`/`SFTP`/`FTPS` to upload your compiled site files to the web server.
Some web hosting services also have web-based file managers.

To deploy your site using any of these methods, all you need to do is upload the entire contents of your `_site`
directory to the web server's public document root, which is usually the `public_html`, `htdocs`, or `www` directory.

### GitHub Pages - Manually

GitHub Pages is a free service that allows you to host your static site on the web.

In general, push the entire contents of your `_site` directory to the `gh-pages` branch of your repository,
or the `docs/` directory on your main branch, depending on how you set it up.

Please see the [GitHub Pages documentation](https://help.github.com/pages/getting-started-with-github-pages/) for more information.

### GitHub Pages - CI/CD

Hyde works amazing with GitHub Pages and GitHub Actions and the entire build and deploy process can be automated.

- We have a great blog post on how to do this, [Automate HydePHP sites using GitHub Actions and GitHub Pages](https://hydephp.com/posts/github-actions-deployment).

- You can also copy our sample [GitHub Actions Workflow.yml file](https://github.com/hyde-staging/ci-demo/blob/master/.github/workflows/main.yml).

By the way, HydePHP.com is hosted on GitHub Pages, and the site is compiled in a GitHub Action workflow that compiles and
deploys the site automatically when the source is updated using [this GitHub workflow](https://github.com/hydephp/hydephp.com/blob/master/.github/workflows/build.yml).

## Next Steps

So what's next? Well, keep creating amazing content, and share it with the world!

We'd love to see your creations, so please share your site with us on Twitter, Facebook, or any other social media platform. Use the hashtag #HydePHP and #MadeWithHydePHP so we can find it!

### SEO Tips

While HydePHP takes most of the hard work out of setting up metadata tags for search engines, there are still a few things you can do to improve your site's SEO.

#### Set a site URL

This is the most important: Make sure you have set a `SITE_URL` either in the `.env` file or in the `hyde.php` configuration file. This is so that Hyde can generate absolute URLs, which are favored by search engines and required for sitemaps and RSS feeds.

#### Sitemaps and RSS Feeds

Next, since HydePHP automatically generates sitemaps and RSS feeds, you should take advantage of these by submitting them to search engines. For example, Google Search Console and Bing Webmaster Tools. This will jumpstart the indexing process, and make sure new pages are indexed quickly. You will also be able to see how your site is performing in search results.

#### Social sharing

Finally, keep sharing your content, so other people can find your site!
