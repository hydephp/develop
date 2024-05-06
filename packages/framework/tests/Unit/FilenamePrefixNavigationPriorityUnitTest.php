<?php

declare(strict_types=1);

use Hyde\Framework\Features\Navigation\FilenamePrefixNavigationHelper;

beforeEach(fn () => $this->mockConfig());

test('enabled returns true when enabled', function () {
    expect(FilenamePrefixNavigationHelper::enabled())->toBeTrue();
});

test('enabled returns false when disabled', function () {
    $this->mockConfig(['hyde.numerical_page_ordering' => false]);

    expect(FilenamePrefixNavigationHelper::enabled())->toBeFalse();
});

test('identifiers with numerical prefixes are detected', function () {
    expect(FilenamePrefixNavigationHelper::isIdentifierNumbered('01-home.md'))->toBeTrue();
    expect(FilenamePrefixNavigationHelper::isIdentifierNumbered('02-about.md'))->toBeTrue();
    expect(FilenamePrefixNavigationHelper::isIdentifierNumbered('03-contact.md'))->toBeTrue();
});

test('identifiers without numerical prefixes are not detected', function () {
    expect(FilenamePrefixNavigationHelper::isIdentifierNumbered('home.md'))->toBeFalse();
    expect(FilenamePrefixNavigationHelper::isIdentifierNumbered('about.md'))->toBeFalse();
    expect(FilenamePrefixNavigationHelper::isIdentifierNumbered('contact.md'))->toBeFalse();
});

test('identifiers with numerical prefixes are detected when using snake case dividers', function () {
    expect(FilenamePrefixNavigationHelper::isIdentifierNumbered('01_home.md'))->toBeTrue();
    expect(FilenamePrefixNavigationHelper::isIdentifierNumbered('02_about.md'))->toBeTrue();
    expect(FilenamePrefixNavigationHelper::isIdentifierNumbered('03_contact.md'))->toBeTrue();
});

test('split number and identifier', function () {
    expect(FilenamePrefixNavigationHelper::splitNumberAndIdentifier('01-home.md'))->toBe([1, 'home.md']);
    expect(FilenamePrefixNavigationHelper::splitNumberAndIdentifier('02-about.md'))->toBe([2, 'about.md']);
    expect(FilenamePrefixNavigationHelper::splitNumberAndIdentifier('03-contact.md'))->toBe([3, 'contact.md']);
});

test('split number and identifier for snake case dividers', function () {
    expect(FilenamePrefixNavigationHelper::splitNumberAndIdentifier('01_home.md'))->toBe([1, 'home.md']);
    expect(FilenamePrefixNavigationHelper::splitNumberAndIdentifier('02_about.md'))->toBe([2, 'about.md']);
    expect(FilenamePrefixNavigationHelper::splitNumberAndIdentifier('03_contact.md'))->toBe([3, 'contact.md']);
});

test('split number and identifier with multiple digits', function () {
    expect(FilenamePrefixNavigationHelper::splitNumberAndIdentifier('123-home.md'))->toBe([123, 'home.md']);
    expect(FilenamePrefixNavigationHelper::splitNumberAndIdentifier('456-about.md'))->toBe([456, 'about.md']);
    expect(FilenamePrefixNavigationHelper::splitNumberAndIdentifier('789-contact.md'))->toBe([789, 'contact.md']);
});

test('split number and identifier with multiple digits and snake case dividers', function () {
    expect(FilenamePrefixNavigationHelper::splitNumberAndIdentifier('123_home.md'))->toBe([123, 'home.md']);
    expect(FilenamePrefixNavigationHelper::splitNumberAndIdentifier('456_about.md'))->toBe([456, 'about.md']);
    expect(FilenamePrefixNavigationHelper::splitNumberAndIdentifier('789_contact.md'))->toBe([789, 'contact.md']);
});

test('split number and identifier throws exception when identifier is not numbered', function () {
    $this->markTestSkipped('Since this is an internal class at the moment, we do not need to test this. If we want this in the public API it should be a badmethodcall exception.');

    $this->expectException(\AssertionError::class);
    $this->expectExceptionMessage('Identifier "home.md" is not numbered.');

    FilenamePrefixNavigationHelper::splitNumberAndIdentifier('home.md');
});

test('identifiers for nested pages with numerical prefixes are detected', function () {
    expect(FilenamePrefixNavigationHelper::isIdentifierNumbered('foo/01-home.md'))->toBeTrue();
    expect(FilenamePrefixNavigationHelper::isIdentifierNumbered('foo/02-about.md'))->toBeTrue();
    expect(FilenamePrefixNavigationHelper::isIdentifierNumbered('foo/03-contact.md'))->toBeTrue();
});

test('identifiers for nested pages with numerical prefixes are detected using snake case dividers', function () {
    expect(FilenamePrefixNavigationHelper::isIdentifierNumbered('foo/01_home.md'))->toBeTrue();
    expect(FilenamePrefixNavigationHelper::isIdentifierNumbered('foo/02_about.md'))->toBeTrue();
    expect(FilenamePrefixNavigationHelper::isIdentifierNumbered('foo/03_contact.md'))->toBeTrue();
});

test('identifiers for nested pages without numerical prefixes are not detected', function () {
    expect(FilenamePrefixNavigationHelper::isIdentifierNumbered('foo/home.md'))->toBeFalse();
    expect(FilenamePrefixNavigationHelper::isIdentifierNumbered('foo/about.md'))->toBeFalse();
    expect(FilenamePrefixNavigationHelper::isIdentifierNumbered('foo/contact.md'))->toBeFalse();
});

test('split number and identifier for nested pages', function () {
    expect(FilenamePrefixNavigationHelper::splitNumberAndIdentifier('foo/01-home.md'))->toBe([1, 'foo/home.md']);
    expect(FilenamePrefixNavigationHelper::splitNumberAndIdentifier('foo/02-about.md'))->toBe([2, 'foo/about.md']);
    expect(FilenamePrefixNavigationHelper::splitNumberAndIdentifier('foo/03-contact.md'))->toBe([3, 'foo/contact.md']);
});

test('split number and identifier for nested pages with snake case dividers', function () {
    expect(FilenamePrefixNavigationHelper::splitNumberAndIdentifier('foo/01_home.md'))->toBe([1, 'foo/home.md']);
    expect(FilenamePrefixNavigationHelper::splitNumberAndIdentifier('foo/02_about.md'))->toBe([2, 'foo/about.md']);
    expect(FilenamePrefixNavigationHelper::splitNumberAndIdentifier('foo/03_contact.md'))->toBe([3, 'foo/contact.md']);
});

test('identifiers for deeply nested pages with numerical prefixes are detected', function () {
    expect(FilenamePrefixNavigationHelper::isIdentifierNumbered('foo/bar/01-home.md'))->toBeTrue();
    expect(FilenamePrefixNavigationHelper::isIdentifierNumbered('foo/bar/02-about.md'))->toBeTrue();
    expect(FilenamePrefixNavigationHelper::isIdentifierNumbered('foo/bar/03-contact.md'))->toBeTrue();
});

test('identifiers for deeply nested pages with numerical prefixes are detected using snake case dividers', function () {
    expect(FilenamePrefixNavigationHelper::isIdentifierNumbered('foo/bar/01_home.md'))->toBeTrue();
    expect(FilenamePrefixNavigationHelper::isIdentifierNumbered('foo/bar/02_about.md'))->toBeTrue();
    expect(FilenamePrefixNavigationHelper::isIdentifierNumbered('foo/bar/03_contact.md'))->toBeTrue();
});

test('identifiers for deeply nested pages without numerical prefixes are not detected', function () {
    expect(FilenamePrefixNavigationHelper::isIdentifierNumbered('foo/bar/home.md'))->toBeFalse();
    expect(FilenamePrefixNavigationHelper::isIdentifierNumbered('foo/bar/about.md'))->toBeFalse();
    expect(FilenamePrefixNavigationHelper::isIdentifierNumbered('foo/bar/contact.md'))->toBeFalse();
});

test('split number and identifier for deeply nested pages', function () {
    expect(FilenamePrefixNavigationHelper::splitNumberAndIdentifier('foo/bar/01-home.md'))->toBe([1, 'foo/bar/home.md']);
    expect(FilenamePrefixNavigationHelper::splitNumberAndIdentifier('foo/bar/02-about.md'))->toBe([2, 'foo/bar/about.md']);
    expect(FilenamePrefixNavigationHelper::splitNumberAndIdentifier('foo/bar/03-contact.md'))->toBe([3, 'foo/bar/contact.md']);
});

test('split number and identifier for deeply nested pages with snake case dividers', function () {
    expect(FilenamePrefixNavigationHelper::splitNumberAndIdentifier('foo/bar/01_home.md'))->toBe([1, 'foo/bar/home.md']);
    expect(FilenamePrefixNavigationHelper::splitNumberAndIdentifier('foo/bar/02_about.md'))->toBe([2, 'foo/bar/about.md']);
    expect(FilenamePrefixNavigationHelper::splitNumberAndIdentifier('foo/bar/03_contact.md'))->toBe([3, 'foo/bar/contact.md']);
});

test('non numerical parts are not detected', function () {
    expect(FilenamePrefixNavigationHelper::isIdentifierNumbered('foo-bar.md'))->toBeFalse();
    expect(FilenamePrefixNavigationHelper::isIdentifierNumbered('foo-bar.md'))->toBeFalse();
    expect(FilenamePrefixNavigationHelper::isIdentifierNumbered('foo-bar.md'))->toBeFalse();

    expect(FilenamePrefixNavigationHelper::isIdentifierNumbered('foo-bar/home.md'))->toBeFalse();
    expect(FilenamePrefixNavigationHelper::isIdentifierNumbered('foo-bar/about.md'))->toBeFalse();
    expect(FilenamePrefixNavigationHelper::isIdentifierNumbered('foo-bar/contact.md'))->toBeFalse();
});

test('non numerical parts are not detected for snake case dividers', function () {
    expect(FilenamePrefixNavigationHelper::isIdentifierNumbered('foo_bar.md'))->toBeFalse();
    expect(FilenamePrefixNavigationHelper::isIdentifierNumbered('foo_bar.md'))->toBeFalse();
    expect(FilenamePrefixNavigationHelper::isIdentifierNumbered('foo_bar.md'))->toBeFalse();

    expect(FilenamePrefixNavigationHelper::isIdentifierNumbered('foo_bar/home.md'))->toBeFalse();
    expect(FilenamePrefixNavigationHelper::isIdentifierNumbered('foo_bar/about.md'))->toBeFalse();
    expect(FilenamePrefixNavigationHelper::isIdentifierNumbered('foo_bar/contact.md'))->toBeFalse();
});

test('numerically prefixed identifiers with unknown dividers are not detected', function () {
    expect(FilenamePrefixNavigationHelper::isIdentifierNumbered('1.foo.md'))->toBeFalse();
    expect(FilenamePrefixNavigationHelper::isIdentifierNumbered('01.foo.md'))->toBeFalse();
    expect(FilenamePrefixNavigationHelper::isIdentifierNumbered('001.foo.md'))->toBeFalse();

    expect(FilenamePrefixNavigationHelper::isIdentifierNumbered('1/foo.md'))->toBeFalse();
    expect(FilenamePrefixNavigationHelper::isIdentifierNumbered('01/foo.md'))->toBeFalse();
    expect(FilenamePrefixNavigationHelper::isIdentifierNumbered('001/foo.md'))->toBeFalse();

    expect(FilenamePrefixNavigationHelper::isIdentifierNumbered('1—foo.md'))->toBeFalse();
    expect(FilenamePrefixNavigationHelper::isIdentifierNumbered('01—foo.md'))->toBeFalse();
    expect(FilenamePrefixNavigationHelper::isIdentifierNumbered('001—foo.md'))->toBeFalse();

    expect(FilenamePrefixNavigationHelper::isIdentifierNumbered('1 foo.md'))->toBeFalse();
    expect(FilenamePrefixNavigationHelper::isIdentifierNumbered('01 foo.md'))->toBeFalse();
    expect(FilenamePrefixNavigationHelper::isIdentifierNumbered('001 foo.md'))->toBeFalse();
});

test('numerically prefixed identifiers without divider are not detected', function () {
    expect(FilenamePrefixNavigationHelper::isIdentifierNumbered('1foo.md'))->toBeFalse();
    expect(FilenamePrefixNavigationHelper::isIdentifierNumbered('01foo.md'))->toBeFalse();
    expect(FilenamePrefixNavigationHelper::isIdentifierNumbered('001foo.md'))->toBeFalse();
});

test('numerically string prefixed identifiers without divider are not detected', function () {
    expect(FilenamePrefixNavigationHelper::isIdentifierNumbered('one-foo.md'))->toBeFalse();
    expect(FilenamePrefixNavigationHelper::isIdentifierNumbered('one_foo.md'))->toBeFalse();
    expect(FilenamePrefixNavigationHelper::isIdentifierNumbered('one.foo.md'))->toBeFalse();
});
