# HydePHP Development Guide for Claude Code

This document serves as a comprehensive reference for Claude Code when working on the HydePHP project. It captures the project structure, coding conventions, and testing patterns to ensure contributions match the established style.

---

## Project Overview

HydePHP is a static site generator built on Laravel Zero. This is the **development monorepo** that contains all HydePHP packages, which are then split and distributed to individual repositories.

**Author:** Emma De Silva (Caen De Silva)
**License:** MIT
**PHP Version:** ^8.2
**Framework:** Laravel Zero ^11.0

---

## Monorepo Structure

```
/packages/
├── framework/          # hyde/framework - Core framework logic
├── hyde/               # hyde/hyde - End-user project template
├── publications/       # hyde/publications - Publications extension
├── ui-kit/             # hyde/ui-kit - Blade component library
├── testing/            # hyde/testing - Testing utilities
├── realtime-compiler/  # hyde/realtime-compiler - Dev server
├── hydefront/          # NPM package - Frontend assets
└── vite-plugin/        # NPM package - Vite integration

/monorepo/
├── DevTools/           # Internal development tools
├── HydeStan/           # Custom static analysis rules
├── CodeIntelligence/   # IDE support generation
└── scripts/            # CI/CD utility scripts

/tests/                 # Root-level test suite
/config/                # Hyde configuration files
/docs/                  # Developer documentation
/app/                   # Application bootstrap
/resources/             # Frontend assets and views
```

### Package Namespaces

| Package | Namespace | Composer Package |
|---------|-----------|------------------|
| Framework | `Hyde\` | `hyde/framework` |
| Hyde | `App\` | `hyde/hyde` |
| Publications | `Hyde\Publications\` | `hyde/publications` |
| UI Kit | `Hyde\UIKit\` | `hyde/ui-kit` |
| Testing | `Hyde\Testing\` | `hyde/testing` |
| Realtime Compiler | `Hyde\RealtimeCompiler\` | `hyde/realtime-compiler` |

---

## Coding Standards

### PHP Version and Strict Types

**Every PHP file MUST begin with:**

```php
<?php

declare(strict_types=1);

namespace Hyde\[Subdomain];
```

### Code Style

- **Standard:** PSR-2 with Laravel conventions
- **Indentation:** 4 spaces (no tabs)
- **Line length:** 120 characters maximum
- **Braces:** Opening brace on new line for classes and methods

### File Structure Template

```php
<?php

declare(strict_types=1);

namespace Hyde\[Subdomain];

// Class imports (alphabetical)
use Hyde\SomeClass;
use Illuminate\Support\Collection;

// Function imports (if needed)
use function array_map;

/**
 * Brief description of the class.
 *
 * @see \Related\ClassName
 */
class ClassName
{
    use SomeTrait;

    // Properties (public, protected, private order)
    protected SomeType $property;

    // Constructor
    public function __construct(SomeType $property)
    {
        $this->property = $property;
    }

    // Public methods
    public function doSomething(): ReturnType
    {
        //
    }

    // Protected methods
    protected function helperMethod(): void
    {
        //
    }

    // Private methods
    private function internalMethod(): void
    {
        //
    }
}
```

### Naming Conventions

**Classes (PascalCase):**
- Standard classes: `MarkdownPage`, `RouteCollection`
- Contracts/Interfaces: `SerializableContract`, `MarkdownDocumentContract`
- Factories: `HydePageDataFactory`, `NavigationDataFactory`
- Exceptions: `FileConflictException`, `RouteNotFoundException`
- Commands: `MakePostCommand`, `BuildCommand`
- Data objects: `NavigationData`, `RenderData`
- Processors: `BladeDownProcessor`, `ShortcodeProcessor`

**Methods (camelCase):**
- Getters: `getLink()`, `getPage()`, `getRouteKey()`
- Setters: `setTitle()`, `setOutput()`
- Boolean checks: `is()`, `has()`, `isHidden()`, `hasChildren()`
- Factory methods: `make()`, `create()`, `from()`
- Finders: `find()`, `findOrFail()`, `get()`
- Actions: `build()`, `compile()`, `render()`

**Properties:**
- Use `readonly` for immutable data objects
- Use typed properties with appropriate visibility

```php
// Readonly properties for data objects
public readonly string $label;
public readonly int $priority;
public readonly ?string $group;

// Protected for internal state
protected HydePage $page;
protected array $data = [];
```

### Type Hints

**Always use full type hints:**

```php
// Parameters and return types
public function process(string $input, ?array $options = null): MarkdownDocument

// Union types when needed
public function find(Route|RouteKey|string $route): ?Route

// Array types documented in docblocks
/**
 * @param  array<string, mixed>  $data
 * @return array<int, Route>
 */
public function transform(array $data): array
```

### DocBlocks

**File-level (for main classes):**
```php
/**
 * Brief description of the class purpose.
 *
 * @see \Hyde\RelatedClass
 *
 * @author  Emma De Silva <emma@desilva.se>
 * @copyright 2022 Emma De Silva
 * @license MIT License
 */
```

**Method-level:**
```php
/**
 * Brief description of what the method does.
 *
 * @param  string  $path  Description of parameter.
 * @return MarkdownDocument  Description of return value.
 *
 * @throws FileNotFoundException  When the file doesn't exist.
 */
```

### Import Organization

1. Class imports (alphabetical)
2. Trait imports (if separate)
3. Function imports (`use function ...`)
4. Constant imports (`use const ...`)

```php
use Hyde\Hyde;
use Hyde\Pages\MarkdownPage;
use Illuminate\Support\Collection;
use Stringable;

use function array_map;
use function str_starts_with;
```

---

## Architecture Patterns

### Facades

Hyde uses facades for convenient static access to services:

```php
// The Hyde facade provides access to HydeKernel
Hyde::path('_posts/hello-world.md');
Hyde::routes()->getRoutes();
```

### Service Providers

Each package has a service provider:

```php
namespace Hyde\Framework;

use Illuminate\Support\ServiceProvider;

class HydeServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        //
    }
}
```

### Data Transfer Objects

Use readonly properties and implement `SerializableContract`:

```php
final class NavigationData extends ArrayObject implements SerializableContract
{
    use Serializable;

    public readonly string $label;
    public readonly int $priority;
    public readonly bool $hidden;
    public readonly ?string $group;

    public function __construct(string $label, int $priority, bool $hidden, ?string $group = null)
    {
        $this->label = $label;
        $this->priority = $priority;
        $this->hidden = $hidden;
        $this->group = $group;
    }
}
```

### Concerns (Traits)

Located in `Concerns` subdirectories:

```php
namespace Hyde\Support\Concerns;

trait Serializable
{
    public function toArray(): array
    {
        return $this->arraySerialize();
    }

    abstract public function arraySerialize(): array;
}
```

### Contracts (Interfaces)

Located in `Contracts` subdirectories, suffixed with `Contract`:

```php
namespace Hyde\Support\Contracts;

interface SerializableContract extends JsonSerializable, Arrayable
{
    public function toArray(): array;
    public function toJson($options = 0): string;
}
```

---

## Testing Conventions

### Test Directory Structure

```
/packages/framework/tests/
├── Feature/              # Integration tests
│   ├── Commands/         # CLI command tests
│   ├── Services/         # Service tests
│   └── Views/            # View/Blade tests
└── Unit/                 # Isolated unit tests

/packages/testing/src/    # Reusable test utilities
├── TestCase.php          # Feature test base class
├── UnitTestCase.php      # Unit test base class
├── CreatesTemporaryFiles.php
├── FluentTestingHelpers.php
├── InteractsWithPages.php
├── ResetsApplication.php
└── MocksKernelFeatures.php
```

### Test Base Classes

**Feature Tests** - Use `TestCase`:

```php
<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature;

use Hyde\Testing\TestCase;

class MyFeatureTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        // Test-specific setup
    }

    public function testFeatureBehavior(): void
    {
        // Test implementation
    }
}
```

**Unit Tests** - Use `UnitTestCase`:

```php
<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit;

use Hyde\Testing\UnitTestCase;

class MyUnitTest extends UnitTestCase
{
    protected static bool $needsKernel = false;
    protected static bool $needsConfig = false;

    public function testUnitBehavior(): void
    {
        // Test implementation
    }
}
```

### Test Naming

- Test classes: `{Subject}Test.php` or `{Subject}UnitTest.php`
- Test methods: `test{Scenario}()` or `test{Method}{Condition}()`

```php
public function testCanGetRouteKey(): void
public function testGetRouteKeyReturnsCorrectFormat(): void
public function testThrowsExceptionWhenFileNotFound(): void
public function testHandlesEmptyInput(): void
```

### Creating Test Files

Use the `CreatesTemporaryFiles` trait:

```php
// Create a single file
$this->file('_pages/test.md', 'content');

// Create multiple files
$this->files([
    '_pages/page1.md' => 'Page 1 content',
    '_pages/page2.md' => 'Page 2 content',
]);

// Create markdown with front matter
$this->markdown('_posts/test.md', 'Body content', [
    'title' => 'Test Post',
    'author' => 'Test Author',
]);

// Create directories
$this->directory('_media/images');
```

### Mocking

**Config mocking:**
```php
self::mockConfig(['hyde.url' => 'https://example.com']);
```

**Page/Route mocking:**
```php
$this->mockPage($page, 'page-key');
$this->mockRoute($route);
$this->mockCurrentPage('docs/index');
```

**Kernel mocking (unit tests):**
```php
protected static bool $needsKernel = true;

public function testSomething(): void
{
    self::mockConfig([...]);
    // Test code
}
```

### Assertions

**Standard PHPUnit:**
```php
$this->assertSame($expected, $actual);      // Strict equality
$this->assertEquals($expected, $actual);    // Loose equality
$this->assertTrue($condition);
$this->assertInstanceOf(ClassName::class, $object);
$this->assertStringContainsString($needle, $haystack);
$this->assertFileExists($path);
```

**Custom assertions:**
```php
$this->assertFileEqualsString($expected, $path);
$this->assertAllSame($var1, $var2, $var3);
```

**Exception testing:**
```php
$this->expectException(ExceptionClass::class);
$this->expectExceptionMessage('Expected message');

$this->methodThatThrows();
```

**Blade view testing:**
```php
$this->view('component.name', ['data' => 'value'])
    ->assertSee('expected text')
    ->assertDontSee('unwanted text')
    ->assertSeeHtml('<div class="expected">');
```

**Artisan command testing:**
```php
$this->artisan('build')
    ->expectsOutput('Building site...')
    ->expectsOutputToContain('Complete')
    ->assertExitCode(0);
```

### Data Providers

Use PHPUnit 10+ attributes:

```php
#[\PHPUnit\Framework\Attributes\DataProvider('pageTypeProvider')]
public function testSupportsMultiplePageTypes(string $pageClass): void
{
    // Test each page type
}

public static function pageTypeProvider(): array
{
    return [
        'markdown page' => [MarkdownPage::class],
        'blade page' => [BladePage::class],
        'documentation page' => [DocumentationPage::class],
    ];
}
```

### Pest Syntax

The project also supports Pest for more expressive tests:

```php
uses(Hyde\Testing\UnitTestCase::class);

test('can create markdown page', function () {
    $page = new MarkdownPage('test');

    expect($page)->toBeInstanceOf(MarkdownPage::class);
    expect($page->identifier)->toBe('test');
});

it('throws when file not found', function () {
    MarkdownPage::get('nonexistent');
})->throws(FileNotFoundException::class);
```

---

## Console Commands

### Command Structure

```php
<?php

declare(strict_types=1);

namespace Hyde\Console\Commands;

use Hyde\Console\Concerns\Command;

class MakePostCommand extends Command
{
    /** @var string */
    protected $signature = 'make:post
        {title? : The title of the post}
        {--force : Overwrite existing file}';

    /** @var string */
    protected $description = 'Create a new blog post';

    public function handle(): int
    {
        $title = $this->argument('title') ?? $this->ask('Title');

        // Command logic...

        $this->info('Post created successfully!');

        return Command::SUCCESS;
    }
}
```

---

## Development Workflow

### Running Tests

```bash
# Run all tests
vendor/bin/pest

# Run specific test suite
vendor/bin/pest --testsuite=UnitFramework
vendor/bin/pest --testsuite=FeatureFramework

# Run with coverage
vendor/bin/pest --coverage

# Run PHPUnit directly
vendor/bin/phpunit
```

### Static Analysis

```bash
# Run Psalm
vendor/bin/psalm

# Run PHPStan
vendor/bin/phpstan analyse
```

### Code Style

The project uses StyleCI for automated style fixes. Ensure code follows PSR-2 with Laravel conventions.

### Building Frontend Assets

```bash
# Development
npm run dev

# Production build
npm run build
```

---

## Key Files Reference

| Purpose | Location |
|---------|----------|
| Main facade | `packages/framework/src/Hyde.php` |
| Kernel | `packages/framework/src/Foundation/HydeKernel.php` |
| Page models | `packages/framework/src/Pages/` |
| Console commands | `packages/framework/src/Console/Commands/` |
| Services | `packages/framework/src/Framework/Services/` |
| Test utilities | `packages/testing/src/` |
| PHPUnit config | `phpunit.xml.dist` |
| Psalm config | `psalm.xml` |

---

## Common Patterns

### Creating a New Page Type

1. Extend `HydePage` or appropriate base class
2. Define `$sourceDirectory` and `$outputDirectory`
3. Implement required abstract methods
4. Register in service provider if needed

### Adding a New Command

1. Create class in `packages/framework/src/Console/Commands/`
2. Extend `Hyde\Console\Concerns\Command`
3. Define `$signature` and `$description`
4. Implement `handle()` method
5. Command auto-discovery handles registration

### Adding a New Service

1. Create service class in appropriate namespace
2. Create facade if needed for static access
3. Register in service provider
4. Add tests for all public methods

---

## Important Notes

- Always run tests before committing
- Follow existing patterns in the codebase
- Use `readonly` properties for data objects
- Implement `SerializableContract` for objects that need serialization
- Prefer composition over inheritance
- Keep methods focused and small
- Document complex logic with inline comments
- Use meaningful variable and method names
