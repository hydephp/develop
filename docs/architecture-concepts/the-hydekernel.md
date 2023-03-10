# The HydeKernel

## Introduction

In the centre, or should I say _core_, of HydePHP is the HydeKernel. The kernel encapsulates a HydePHP project and
provides helpful methods for interacting with it. You can think of it as the heart of HydePHP, if you're a romantic.

The HydeKernel is so important that you have probably used it already. The main entry point for the HydePHP
API is the Hyde facade, which calls methods on the kernel.

```php
use Hyde\Hyde;
use Hyde\Foundation\HydeKernel;

Hyde::version() === app(HydeKernel::class)->version();
```

The kernel is created very early on in the application lifecycle, in the bootstrap.php file, where it is also bound
as a singleton into the application service container.

