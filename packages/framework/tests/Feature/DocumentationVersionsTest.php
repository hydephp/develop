<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature;

use Hyde\Hyde;
use Hyde\Testing\TestCase;
use Hyde\Pages\DocumentationPage;
use Hyde\Framework\Exceptions\InvalidConfigurationException;
use Hyde\Framework\Features\Documentation\Versioning\DocumentationVersion;
use Hyde\Framework\Features\Documentation\Versioning\DocumentationVersions;

#[\PHPUnit\Framework\Attributes\CoversClass(DocumentationVersion::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(DocumentationVersions::class)]
class DocumentationVersionsTest extends TestCase
{
    public function testVersioningIsDisabledByDefault()
    {
        $this->assertFalse(DocumentationVersions::enabled());
    }

    public function testVersioningIsEnabledWhenVersionsAreRegistered()
    {
        config(['docs.versions' => ['1.x', '2.x']]);

        $this->assertTrue(DocumentationVersions::enabled());
    }

    public function testAllReturnsEmptyCollectionWhenDisabled()
    {
        $this->assertTrue(DocumentationVersions::all()->isEmpty());
    }

    public function testAllReturnsRegisteredVersionsKeyedByName()
    {
        config(['docs.versions' => ['1.x', '2.x']]);

        $versions = DocumentationVersions::all();

        $this->assertCount(2, $versions);
        $this->assertSame(['1.x', '2.x'], $versions->keys()->all());
        $this->assertContainsOnlyInstancesOf(DocumentationVersion::class, $versions);
    }

    public function testDefaultVersionIsTheLastEntryByDefault()
    {
        config(['docs.versions' => ['1.x', '2.x']]);

        $this->assertSame('2.x', DocumentationVersions::default()->name);
    }

    public function testDefaultVersionCanBeSetExplicitly()
    {
        config(['docs.versions' => ['1.x', '2.x'], 'docs.default_version' => '1.x']);

        $this->assertSame('1.x', DocumentationVersions::default()->name);
    }

    public function testDefaultVersionIsNullWhenVersioningIsDisabled()
    {
        $this->assertNull(DocumentationVersions::default());
    }

    public function testDefaultVersionNameReturnsNullForEmptyVersionList()
    {
        $method = new \ReflectionMethod(DocumentationVersions::class, 'defaultVersionName');
        $method->setAccessible(true);

        $this->assertNull($method->invoke(null, []));
    }

    public function testUnknownDefaultVersionThrows()
    {
        config(['docs.versions' => ['1.x'], 'docs.default_version' => '2.x']);

        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessage("The default documentation version '2.x' is not present in the `docs.versions` configuration.");

        DocumentationVersions::default();
    }

    public function testInvalidVersionNameThrows()
    {
        config(['docs.versions' => ['1.x/nested']]);

        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessage("Invalid documentation version name '1.x/nested'.");

        DocumentationVersions::all();
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('validVersionNameProvider')]
    public function testValidVersionNamesAreAccepted(string $name)
    {
        config(['docs.versions' => [$name]]);

        $this->assertSame($name, DocumentationVersions::get($name)->name);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('invalidVersionNameProvider')]
    public function testInvalidVersionNamesAreRejected(string $name)
    {
        config(['docs.versions' => [$name]]);

        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessage("Invalid documentation version name '$name'.");

        DocumentationVersions::all();
    }

    public function testGetReturnsNullForUnregisteredVersion()
    {
        config(['docs.versions' => ['1.x']]);

        $this->assertNull(DocumentationVersions::get('2.x'));
    }

    public function testFromIdentifierResolvesVersionFromFirstPathSegment()
    {
        config(['docs.versions' => ['1.x', '2.x']]);

        $this->assertSame('1.x', DocumentationVersions::fromIdentifier('1.x/installation')->name);
        $this->assertSame('2.x', DocumentationVersions::fromIdentifier('2.x/getting-started/installation')->name);
    }

    public function testFromIdentifierReturnsNullForUnversionedIdentifiers()
    {
        config(['docs.versions' => ['1.x', '2.x']]);

        $this->assertNull(DocumentationVersions::fromIdentifier('installation'));
        $this->assertNull(DocumentationVersions::fromIdentifier('getting-started/installation'));
        $this->assertNull(DocumentationVersions::fromIdentifier('1.x'));
    }

    public function testFromIdentifierReturnsNullWhenVersioningIsDisabled()
    {
        $this->assertNull(DocumentationVersions::fromIdentifier('1.x/installation'));
    }

    public function testFromRouteKeyResolvesVersion()
    {
        config(['docs.versions' => ['1.x', '2.x']]);

        $this->assertSame('1.x', DocumentationVersions::fromRouteKey('docs/1.x/installation')->name);
        $this->assertNull(DocumentationVersions::fromRouteKey('docs/installation'));
        $this->assertNull(DocumentationVersions::fromRouteKey('posts/1.x/installation'));
    }

    public function testStripVersionPrefix()
    {
        config(['docs.versions' => ['1.x', '2.x']]);

        $this->assertSame('installation', DocumentationVersions::stripVersionPrefix('1.x/installation'));
        $this->assertSame('getting-started/installation', DocumentationVersions::stripVersionPrefix('2.x/getting-started/installation'));
        $this->assertSame('installation', DocumentationVersions::stripVersionPrefix('installation'));
        $this->assertSame('3.x/installation', DocumentationVersions::stripVersionPrefix('3.x/installation'));
    }

    public function testStripVersionPrefixFromRouteKey()
    {
        config(['docs.versions' => ['1.x', '2.x']]);

        $this->assertSame('docs/installation', DocumentationVersions::stripVersionPrefixFromRouteKey('docs/1.x/installation'));
        $this->assertSame('docs/getting-started/installation', DocumentationVersions::stripVersionPrefixFromRouteKey('docs/2.x/getting-started/installation'));
        $this->assertSame('docs/installation', DocumentationVersions::stripVersionPrefixFromRouteKey('docs/installation'));
        $this->assertSame('posts/1.x/installation', DocumentationVersions::stripVersionPrefixFromRouteKey('posts/1.x/installation'));
    }

    public function testStripVersionPrefixFromRouteKeyRespectsCustomOutputDirectory()
    {
        config(['docs.versions' => ['1.x']]);

        DocumentationPage::setOutputDirectory('reference');

        try {
            $this->assertSame('reference/installation', DocumentationVersions::stripVersionPrefixFromRouteKey('reference/1.x/installation'));
        } finally {
            DocumentationPage::setOutputDirectory('docs');
        }
    }

    public function testStripVersionPrefixFromRouteKeyReturnsInputWhenVersioningIsDisabled()
    {
        $this->assertSame('docs/1.x/installation', DocumentationVersions::stripVersionPrefixFromRouteKey('docs/1.x/installation'));
    }

    public function testVersionRouteKeyPrefixAndHomeRouteName()
    {
        config(['docs.versions' => ['1.x']]);

        $version = DocumentationVersions::get('1.x');

        $this->assertSame('docs/1.x', $version->routeKeyPrefix());
        $this->assertSame('docs/1.x/index', $version->homeRouteName());
    }

    public function testVersionRouteKeyPrefixRespectsCustomOutputDirectory()
    {
        config(['docs.versions' => ['1.x']]);

        DocumentationPage::setOutputDirectory('documentation');

        $this->assertSame('documentation/1.x', DocumentationVersions::get('1.x')->routeKeyPrefix());
    }

    public function testVersionCastsToStringName()
    {
        $this->assertSame('1.x', (string) new DocumentationVersion('1.x'));
    }

    public function testGetEquivalentRouteFindsPageInOtherVersion()
    {
        config(['docs.versions' => ['1.x', '2.x'], 'docs.flattened_output_paths' => false]);

        $this->file('_docs/1.x/installation.md');
        $this->file('_docs/2.x/installation.md');
        $this->file('_docs/2.x/upgrading.md');

        $this->rediscoverPages();

        $page = DocumentationPage::get('2.x/installation');

        $route = DocumentationVersions::getEquivalentRoute($page, DocumentationVersions::get('1.x'));

        $this->assertNotNull($route);
        $this->assertSame('docs/1.x/installation', $route->getRouteKey());

        $this->assertNull(DocumentationVersions::getEquivalentRoute(DocumentationPage::get('2.x/upgrading'), DocumentationVersions::get('1.x')));
    }

    public function testGetEquivalentRouteFindsFlattenedPageInOtherVersion()
    {
        config(['docs.versions' => ['1.x', '2.x']]);

        $this->file('_docs/1.x/getting-started/installation.md');
        $this->file('_docs/2.x/getting-started/installation.md');

        $this->rediscoverPages();

        $route = DocumentationVersions::getEquivalentRoute(DocumentationPage::get('2.x/getting-started/installation'), DocumentationVersions::get('1.x'));

        $this->assertNotNull($route);
        $this->assertSame('docs/1.x/installation', $route->getRouteKey());
    }

    public function testGetEquivalentRouteReturnsNullForPagesThatAreNotInAVersion()
    {
        config(['docs.versions' => ['1.x', '2.x']]);

        $this->file('_docs/1.x/shared.md');

        $this->rediscoverPages();

        $this->assertNull(DocumentationVersions::getEquivalentRoute(new DocumentationPage('shared'), DocumentationVersions::get('1.x')));
    }

    public static function validVersionNameProvider(): array
    {
        return [
            ['1.x'],
            ['v2'],
            ['2026.07'],
            ['beta-1'],
            ['rc_1'],
        ];
    }

    protected function rediscoverPages(): void
    {
        Hyde::boot();
    }

    public static function invalidVersionNameProvider(): array
    {
        return [
            [''],
            ['1.x/nested'],
            ['.hidden'],
            ['-draft'],
            ['_internal'],
            ['release candidate'],
        ];
    }
}
