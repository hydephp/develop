# Autodiscovery

## Introduction

HydePHP aims to reduce the amount of configuration you need to do to get a site up and running.
To that end, Hyde uses a process called autodiscovery to automatically find and register your pages.

This article will go into detail about how autodiscovery works as well as the lifecycle of a site build.

### Prerequisites

Before reading this article, you should be familiar with the following concepts:
-  [Page Models](page-models)

## The short version

Hyde will use the information in the page model classes to scan the source directories for matching files which are
parsed using instructions from the model's class, resulting in data used to construct objects that get stored in the HydeKernel.
