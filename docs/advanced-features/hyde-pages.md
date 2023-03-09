---
navigation:
    label: "HydePage API"
---

# HydePage API Reference

>warning This article covers advanced information, and you are expected to already be familiar with the [Page Models](page-models).
 
## Abstract

This page contains the full API references for the built-in HydePage classes. Most users will not need to know about
the inner workings of classes, but if you're interested in extending HydePHP, or just curious, this page is for you.
It is especially useful if you're looking to implement your own page classes, or if you are creating advanced Blade templates.

### Table of Contents

| Class                                   | Description                            |
|-----------------------------------------|----------------------------------------|
| [HydePage](#hydepage)                   | The base class for all Hyde pages.     |
| [BaseMarkdownPage](#basemarkdownpage)   | The base class for all Markdown pages. |
| [InMemoryPage](#inmemorypage)           | Extendable class for in-memory pages.  |
| [HtmlPage](#basepage)                   | Class for HTML pages.                  |
| [BladePage](#markdownpage)              | Class for Blade pages.                 |
| [MarkdownPage](#markdownpage)           | Class for Markdown pages.              |
| [MarkdownPost](#markdownpost)           | Class for Markdown posts.              |
| [DocumentationPage](#documentationpage) | Class for documentation pages.         |

