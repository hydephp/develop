# Dynamic Data Discovery

## Introduction

Hyde wants to allow developers to write less, and do more. This is also a major difference between HydePHP and JekyllRB.
Jekyll will only do what you _tell it to do_. Hyde, on the other hand, will try to do what you _want it to do_.

## Front Matter (And Filling in the Gaps)

Hyde makes great use of front matter in both Markdown and Blade files (it's true!). However, it can quickly get tedious
and quite frankly plain boring to have to write a bunch of front matter all the time. As Hyde wants you to focus on
your content, and not your markup, front matter is optional and Hyde will try to fill in the gaps for you.

If you're not happy with Hyde's generated data you can always override it by adding front matter to your files.

### How it Works

Now, to the fun part: getting into the nitty-gritty details of how Hyde does this. 
