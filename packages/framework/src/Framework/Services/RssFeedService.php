<?php

/** @noinspection PhpComposerExtensionStubsInspection */
/** @noinspection XmlUnusedNamespaceDeclaration */

declare(strict_types=1);

namespace Hyde\Framework\Services;

use Exception;
use Hyde\Hyde;
use Hyde\Pages\MarkdownPost;
use Hyde\Support\Helpers\XML;
use SimpleXMLElement;

/**
 * @see \Hyde\Framework\Testing\Feature\Services\RssFeedServiceTest
 * @see https://validator.w3.org/feed/docs/rss2.html
 * @phpstan-consistent-constructor
 */
class RssFeedService
{
    public SimpleXMLElement $feed;

    public function __construct()
    {
        if (! extension_loaded('simplexml') || config('testing.mock_disabled_extensions', false) === true) {
            throw new Exception('The ext-simplexml extension is not installed, but is required to generate RSS feeds.');
        }

        $this->feed = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?>
            <rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom" xmlns:dc="http://purl.org/dc/elements/1.1/" />');
        $this->feed->addChild('channel');

        $this->addInitialChannelItems();
    }

    /**
     * @throws \Exception
     */
    public function generate(): static
    {
        /** @var \Hyde\Pages\MarkdownPost $post */
        foreach (MarkdownPost::getLatestPosts() as $post) {
            $this->addItem($post);
        }

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
        if ($post->canonicalUrl !== null) {
            $item->addChild('link', $post->canonicalUrl);
            $item->addChild('guid', $post->canonicalUrl);
        }
        $item->addChild('description', $post->description);

        $this->addAdditionalItemData($item, $post);
    }

    protected function addAdditionalItemData(SimpleXMLElement $item, MarkdownPost $post): void
    {
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
            $image->addAttribute('url', Hyde::image((string) $post->image, true));
            $image->addAttribute('type', str_ends_with($post->image->getSource(), '.png') ? 'image/png' : 'image/jpeg');
            $image->addAttribute('length', (string) $post->image->getContentLength());
        }
    }

    protected function addInitialChannelItems(): void
    {
        $this->feed->channel->addChild('title', static::getTitle());
        $this->feed->channel->addChild('link', static::getLink());
        $this->feed->channel->addChild('description', static::getDescription());

        $atomLink = $this->feed->channel->addChild('atom:link', namespace: 'http://www.w3.org/2005/Atom');
        $atomLink->addAttribute('href', static::getLink().'/'.static::getDefaultOutputFilename());
        $atomLink->addAttribute('rel', 'self');
        $atomLink->addAttribute('type', 'application/rss+xml');

        $this->addAdditionalChannelData();
    }

    protected function addAdditionalChannelData(): void
    {
        $this->feed->channel->addChild('language', config('site.language', 'en'));
        $this->feed->channel->addChild('generator', 'HydePHP '.Hyde::version());
        $this->feed->channel->addChild('lastBuildDate', date(DATE_RSS));
    }

    public static function getDescription(): string
    {
        return XML::escape(config(
            'hyde.rss_description',
            static::getTitle() . ' RSS Feed'
        ));
    }

    public static function getTitle(): string
    {
        return XML::escape(config('site.name', 'HydePHP'));
    }

    public static function getLink(): string
    {
        return XML::escape(rtrim(
            config('site.url') ?? 'http://localhost',
            '/'
        ));
    }

    public static function getDefaultOutputFilename(): string
    {
        return config('hyde.rss_filename', 'feed.xml');
    }

    public static function generateFeed(): string
    {
        return (new static)->generate()->getXML();
    }
}
