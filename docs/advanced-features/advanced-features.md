---
navigation:
    label: Introduction
    priority: 40
---

# Advanced Features in HydePHP

## Preface

HydePHP is a simple, yet powerful, static site generator. It is designed to be easy to use and easy to extend.

This section of the documentation will cover some of the more advanced (but optional) features of the framework.


## Prerequisites

To fully understand the features described in these chapters, it may be beneficial to first skim through the
[Architecture Concepts](architecture-concepts) chapters, or at the very least, the [Core Concepts](core-concepts) page.

You are also expected to have a basic understanding of PHP, and object-oriented programming principles.
Having some familiarity with Laravel will also be beneficial, as HydePHP is built on top of the Laravel framework.


## Table of Contents

[Blade]: <ul>@foreach(glob(DocumentationPage::path('advanced-features/*.md')) as $file) <li> <a href="{{ basename($file, '.md') }}.html"> {{ Hyde::makeTitle(basename($file, '.md')) }} </a> </li> @endforeach</ul>
