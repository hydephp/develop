<?php

/** @noinspection PhpComposerExtensionStubsInspection */
/** @noinspection XmlUnusedNamespaceDeclaration */

declare(strict_types=1);

namespace Hyde\Framework\Services;

use Exception;
use Hyde\Facades\Site;
use Hyde\Hyde;
use Hyde\Pages\MarkdownPost;
use Hyde\Support\Helpers\XML;
use SimpleXMLElement;
use function config;
use function date;
use function extension_loaded;

/**
 * @see \Hyde\Framework\Testing\Feature\Services\RssFeedServiceTest
 * @see https://validator.w3.org/feed/docs/rss2.html
 * @phpstan-consistent-constructor
 */
class RssFeedService
{
    public SimpleXMLElement $feed;

    public static function generateFeed(): string
    {
        return (new static)->generate()->getXML();
    }

    public static function outputFilename(): string
    {
        return config('hyde.rss_filename', 'feed.xml');
    }

    public static function getDescription(): string
    {
        return XML::escape(config(
            'hyde.rss_description',
            static::getTitle().' RSS Feed'
        ));
    }

    public function __construct()
    {
        if (! extension_loaded('simplexml') || config('testing.mock_disabled_extensions', false) === true) {
            throw new Exception('The ext-simplexml extension is not installed, but is required to generate RSS feeds.');
        }

        $this->feed = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?>
            <rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom" xmlns:dc="http://purl.org/dc/elements/1.1/" />');
        $this->feed->addChild('channel');

        $this->addBaseChannelItems();
    }

    public function generate(): static
    {
        MarkdownPost::getLatestPosts()
            ->each(fn(MarkdownPost $post) => $this->addItem($post));

        return $this;
    }

    public function getXML(): string
    {
        return (string) $this->feed->asXML();
    }

    protected function addItem(MarkdownPost $post): void
    {
        $item = $this->feed->channel->addChild('item');
        $item->addChild('title', $post->title);
        $item->addChild('description', $post->description);

        $this->addDynamicItemData($item, $post);
    }

    protected function addDynamicItemData(SimpleXMLElement $item, MarkdownPost $post): void
    {
        if (isset($post->canonicalUrl)) {
            $item->addChild('link', $post->canonicalUrl);
            $item->addChild('guid', $post->canonicalUrl);
        }

        if (isset($post->date)) {
            $item->addChild('pubDate', $post->date->dateTimeObject->format(DATE_RSS));
        }

        if (isset($post->author)) {
            $item->addChild('dc:creator', $post->author->getName(), 'http://purl.org/dc/elements/1.1/');
        }

        if (isset($post->category)) {
            $item->addChild('category', $post->category);
        }

        if (isset($post->image)) {
            $image = $item->addChild('enclosure');
            $image->addAttribute('url', Hyde::image($post->image->getSource(), true));
            $image->addAttribute('type', $this->getImageType($post));
            $image->addAttribute('length', $this->getImageLength($post));
        }
    }

    protected function addBaseChannelItems(): void
    {
        $this->feed->channel->addChild('title', static::getTitle());
        $this->feed->channel->addChild('link', static::getLink());
        $this->feed->channel->addChild('description', static::getDescription());

        $this->feed->channel->addChild('language', config('site.language', 'en'));
        $this->feed->channel->addChild('generator', 'HydePHP '.Hyde::version());
        $this->feed->channel->addChild('lastBuildDate', date(DATE_RSS));

        $atomLink = $this->feed->channel->addChild('atom:link', namespace: 'http://www.w3.org/2005/Atom');
        $atomLink->addAttribute('href', static::getLink().'/'.static::outputFilename());
        $atomLink->addAttribute('rel', 'self');
        $atomLink->addAttribute('type', 'application/rss+xml');
    }

    protected static function getTitle(): string
    {
        return XML::escape(Site::name());
    }

    protected static function getLink(): string
    {
        return XML::escape(Site::url());
    }

    protected function getImageType(MarkdownPost $post): string
    {
        /** @todo Add support for more types */
        return str_ends_with($post->image->getSource(), '.png') ? 'image/png' : 'image/jpeg';
    }

    protected function getImageLength(MarkdownPost $post): string
    {
        /** @todo We might want to add a build warning if the length is zero */
        return (string) $post->image->getContentLength();
    }
}
