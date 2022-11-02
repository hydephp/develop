---
navigation:
    label: Introduction
    priority: 40
---

# Advanced Features in HydePHP

## Preface

HydePHP is a simple, yet powerful, static site generator. It is designed to be easy to use and easy to extend.
This section of the documentation will cover some of the more advanced features of the framework.

## Introduction

While the goal of Hyde is to allow developers to quickly and easily create static sites with minimal effort,
the engine behind it is actually quite powerful. In addition to being based on Laravel, HydePHP has some
special features that you as a user of Hyde might already have been using, without even knowing it.

You, of course, are free to skip this entire section, as you don't need to know these things to use Hyde.
However, if you want to know the "magic" behind Hyde, or if you want to take advantage of these powerful tools,
then by all means, please read on! This is also a great place to start if you want to contribute to the source code.

## Behind the magic

Want to learn more about a particular feature? Click on the links below to visit the article.

[//]: # (This would be better suited for a component, but it's a fun experiment for now)
[Blade]: <ul>@foreach(glob(\Hyde\Hyde::path('docs/advanced-features/*.md')) as $file) <li> <a href="{{ basename($file, '.md') }}.html"> {{ \Hyde\Hyde::makeTitle(basename($file, '.md')) }} </a> </li> @endforeach</ul>


### Disclaimer

These chapters are written for power users and contributors. If you're just looking to get a site up and running,
you can safely skip this section. The documentation here will cover advanced topics under the presumption that
the reader has a basic to intermediate understanding of programming, as well as PHP and to some extent Laravel.
