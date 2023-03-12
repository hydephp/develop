# Elegant and Powerful Static Site Generator

<style>.images-inline img { display: inline; margin: 4px 2px;}</style>

![Latest Version on Packagist](https://img.shields.io/packagist/v/hyde/framework?include_prereleases)
![Total Downloads on Packagist](https://img.shields.io/packagist/dt/hyde/framework)
[![Test Coverage](https://codecov.io/gh/hydephp/develop/branch/master/graph/badge.svg?token=G6N2161TOT)](https://codecov.io/gh/hydephp/develop)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/hydephp/develop/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/hydephp/develop/?branch=master)
[![Psalm Type Coverage](https://shepherd.dev/github/hydephp/develop/coverage.svg)](https://shepherd.dev/github/hydephp/develop)
![License MIT](https://img.shields.io/github/license/hydephp/hyde)
{.images-inline .not-prose}


## About HydePHP

HydePHP is a new Static Site Builder focused on writing content, not markup. With Hyde, it is easy to create static
websites, blogs, and documentation pages using Markdown and (optionally) Laravel's Blade.

Operated entirely through the command-line, HydePHP provides developers with a fast and efficient way to create high-quality websites with ease.
Unlike traditional web development frameworks, sites compiled with HydePHP don't require any server to run,
making it an ideal choice for building lightweight and fast-loading websites.

Compared with other static site builders, Hyde is blazingly fast and seriously simple to get started with, yet it has the
full power of Laravel waiting for you when you need it, as Hyde is powered by Laravel Zero, a stripped-down version of
the robust and popular Laravel Framework, optimized for console applications.

Hyde makes creating websites easy and fun by taking care of the boring stuff, like routing, writing boilerplate, and
endless configuration. Instead, when you create a new Hyde project, everything you need to get started is already there
-- including precompiled TailwindCSS, well-crafted Blade templates, and easy-to-use asset management.

Hyde was inspired by JekyllRB and is designed for developers who are comfortable writing posts in Markdown, and it requires
virtually no configuration out of the box as it favours convention over configuration and is preconfigured with sensible defaults.


## Installation

HydePHP is a command-line interface (CLI) application that is installed on a per-project basis.

To use HydePHP, your system must have PHP version 8.1 or later installed, along with Composer, and access to a terminal.

The recommended method of installation is using Composer.

```bash
composer create-project hyde/hyde --stability=rc
```

Once installed, you can access the HydeCLI from the project root using the `hyde` command.

```bash
php hyde info
```

### To learn more, head over to the [quickstart page](quickstart).
