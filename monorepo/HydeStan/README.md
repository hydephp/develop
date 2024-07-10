# HydeStan - Experimental Custom Static Analysis Tool for the HydePHP Monorepo

## About

HydeStan is a custom static analysis tool in the HydePHP monorepo, designed to provide additional static analysis and code quality checks for the HydePHP framework.

The tool is in continuous development and is highly specialized, and cannot be relied upon for general purpose static analysis outside this repository.

## Scope

The analyser is called through the `run.php` script, and is automatically run on all commits through the GitHub Actions CI/CD pipeline.

It can also be run manually from the monorepo root:

```bash
php ./monorepo/HydeStan/run.php
```

A subset of HydeStan is also run on the Git patches sent to our custom CI Server at https://ci.hydephp.com to provide immediate feedback on commits.

