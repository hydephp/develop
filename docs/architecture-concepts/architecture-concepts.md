---
navigation:
    label: Introduction
    priority: 50
---

# Advanced Architecture Concepts

## Introduction

These chapters are written for power users and contributors. If you're just looking to get a site up and running,
you can safely skip this section. The documentation here will cover advanced topics under the presumption that
the reader has a basic to intermediate understanding of programming, as well as PHP and to some extent Laravel.

You, of course, are free to skip this entire section, as you don't need to know these things to use Hyde.
However, if you want to know the "magic" behind Hyde, or if you want to take advantage of these powerful tools,
then by all means, please read on! This is also a great place to start if you want to contribute to the source code.

>info For a high-level overview of these concepts, see the [Basic Architecture Concepts](basic-architecture-concepts.html) page.

## Behind the magic

Want to learn more about a particular feature? Click on the links below to visit the article.

[//]: # (This would be better suited for a component, but it's a fun experiment for now)
[Blade]: <ul>@foreach(glob(\Hyde\Hyde::path('docs/architecture-concepts/*.md')) as $file) <li> <a href="{{ basename($file, '.md') }}.html"> {{ Hyde::makeTitle(basename($file, '.md')) }} </a> </li> @endforeach</ul>

