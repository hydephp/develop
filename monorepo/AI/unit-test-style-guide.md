# HydePHP Unit Test Case Style Guide

This is an example of how we write unit tests in HydePHP. The code comments should not be included in the actual test files, but are included here for reference.

## Base Class Structure

- Unit tests for the Framework package go in `packages/framework/tests/Unit/UnitTestName.php` in the monorepo
- This uses the namespace `Hyde\Framework\Testing\Unit`
- The class should extend `Hyde\Framework\Testing\Unit\TestCase`
- The class should have a `@covers` annotation for the class being tested
- If you know that there is also a related feature test, you can add a `@see` annotation to link to it
- Make sure to use a strict type declaration at the top of the file

```php
<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit;

use Hyde\Testing\UnitTestCase;

/**
 * @covers \Hyde\Hyde
 */
class ExampleUnitTest extends UnitTestCase
{
    public function testExample()
    {
        $this->assertTrue(true);
    }
}
```

## Method Structure

### Test Methods

#### Naming

- We use the `test` prefix for all test methods
- Test methods should be in camelCase
- Test methods should be public
- Test methods should not have return types
- Test methods should not have parameters unless a data provider is used

### Helper Methods

We may use helper methods in our test classes to reduce duplication. These methods should be protected and should have return types. If parameters are used, they should be type hinted.

```php
protected function createExample(array $configuration = []): Example
{
    return new Example($configuration);
}
```

Helper methods should be placed at the bottom of the class.

### Data Providers

If you need to run the same test with multiple sets of data, you can use a data provider. Data providers should be public static and should return an array of arrays. The test method should accept parameters for the data.

```php
/**
 * @dataProvider exampleDataProvider
 */
public function testExample(string $input, string $expected)
{
    $this->assertSame($expected, $input);
}

public static function exampleDataProvider(): array
{
    return [
        ['foo', 'bar'],
        ['baz', 'qux'],
    ];
}
```

Data providers should be placed at the bottom of the class, after helper methods.

### Setup and Teardown

If you need to run code before or after each test, you can use the `setUp` and `tearDown` methods. These methods should be protected and return void.

```php
protected function setUp(): void
{
    // Code to run before each test
}

protected function tearDown(): void
{
    // Code to run after each test
}
``` 

You do not need to call the parent methods in your setup and teardown methods as those methods are empty in the base test case class.

### Assertions

- We use PHPUnit assertions for our tests
- We use the `assertSame` assertion for comparing values of the same type. This includes all scalar types, arrays, and simple types.
- We use the `assertEquals` assertion for comparing values of objects which are not expected to be identical (the same instance).

We prefer semantic assertions when possible. For example (but not limited to):
- We use the `assertTrue` and `assertFalse` assertions for boolean values.
- We use the `assertNull` and `assertNotNull` assertions for null values.
- We use the `assertEmpty` and `assertNotEmpty` assertions for empty values.

### Testing Helpers

Some parts of the codebase require the HydeKernel and/or the configuration class to be set up before running tests. We have helper properties to make this easier.

```php
class ExampleUnitTest extends UnitTestCase
{
    protected static bool $needsKernel = true; // Add if you need the kernel
    protected static bool $needsConfig = true; // Add if you need the Laravel config
    
    public function testExample()
    {
        $this->assertTrue(true);
    }
}
```

You can also mock a config value using the mockConfig helper:

```php
public function testExample()
{
    self::mockConfig('example.key', 'example value');
    $this->assertSame('example value', config('example.key'));
}
```

## Testing style

In unit tests, we tend to test the public methods or a class, in order to ensure the implementations are correct.
Then in feature tests we can focus on the higher levels, with confidence that the implementations are correct and function as expected.

A common way we do this is that we first run tests with the expected input(s) and output(s), to test all the happy paths.
Then we run tests with unexpected input(s) and output(s), to test all the unhappy paths.

Having the happy paths first makes the test readable, and people can look at the test to find out what the expected behavior is supposed to be as the tests serve as the source of truth.
This also makes it easier to see if we are not following the documented specifications by comparing the tests to the documentation.

