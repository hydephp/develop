# How does it work

## Hyde is installed similarly to Laravel
Installation is easy by using composer. The installer creates a directory with all the files you need to get started, including Blade views and compiled Tailwind assets.

## Creating content is easy with Markdown
Put your Markdown or Blade content in the source directories. The directory you use will determine the Blade layout and output path that will be used. 

> For example, a file stored as `_posts/hello-world.md` will be compiled using the `post.blade.php` layout and saved as `_site/posts/hello-world.html`.

## Compiling is initiated with the HydeCLI
When running the build command, Hyde will take your source files and intelligently compiles them into the output directory. During this build navigation menus and sidebars will be generated automatically.

