# HydeStan - Internal Custom Static Analysis for the HydePHP Monorepo

## About

HydeStan is a custom static analysis tool in the HydePHP monorepo, designed to provide additional static analysis and code quality checks for the HydePHP framework.
It is in continuous development and is highly specialized, and cannot be relied upon for any outside this repository.

## Usage

The analyser is called through the `run.php` script, and is automatically run on all commits through the GitHub Actions CI/CD pipeline.

### Running HydeStan

It can also be run manually from the monorepo root:

```bash
php ./monorepo/HydeStan/run.php
```

### GitHub Integration

A subset of HydeStan is also run on the Git patches sent to our custom [CI Server](https://ci.hydephp.com) to provide near-instant immediate feedback on commits.
Example: https://ci.hydephp.com/api/hydestan/status/e963e2b1c8637ed5d1114e98b32ee698a821c74f
