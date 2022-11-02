# Autodiscovery

## Introduction

HydePHP aims to reduce the amount of configuration you need to do to get a site up and running.
To that end, Hyde uses a process called autodiscovery to automatically find and register your pages.

This article will go into detail about how autodiscovery works as well as the lifecycle of a site build.

### Prerequisites

Before reading this article, you should be familiar with the following concepts:
-  [Page Models](page-models)

## The short version

When booting up Hyde, it will using the information in the registered page models scan the configured
source directories for files that match the file extension of the page model. These files are then parsed using
instructions from the page model, and the resulting data is stored in a new instance of the page model, all
of which are stored in the HydeKernel's `pages` collection.
