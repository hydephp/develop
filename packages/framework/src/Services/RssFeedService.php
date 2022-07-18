<?php

namespace Hyde\Framework\Services;

use Hyde\Framework\Helpers\Features;
use Hyde\Framework\Hyde;
use Hyde\Framework\Models\Pages\MarkdownPost;
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
        $this->feed = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?>
            <rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom" xmlns:dc="http://purl.org/dc/elements/1.1/" />');
        $this->feed->addChild('channel');

        $this->addInitialChannelItems();
    }

    /**
     * @throws \Exception
     */
    public function generate(): self
    {
        /** @var MarkdownPost $post */
        foreach (MarkdownPost::getLatestPosts() as $post) {
            $this->addItem($post);
        }

        return $this;
    }

    public function getXML(): string
    {
        return $this->feed->asXML();
    }

    protected function addItem(MarkdownPost $post): void
    {
        $item = $this->feed->channel->addChild('item');
        $item->addChild('title', $post->findTitleForDocument());
        $item->addChild('link', $post->getCanonicalLink());
        $item->addChild('guid', $post->getCanonicalLink());
        $item->addChild('description', $post->getPostDescription());

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
            $image->addAttribute('url', isset($post->image->path) ? Hyde::uriPath('media/'.basename($post->image->path)) : $post->image->getSource());
            $image->addAttribute('type', str_ends_with($post->image->getSource(), '.png') ? 'image/png' : 'image/jpeg');
            $image->addAttribute('length', $post->image->getContentLength());
        }
    }

    protected function addInitialChannelItems(): void
    {
        $this->feed->channel->addChild('title', static::getTitle());
        $this->feed->channel->addChild('link', static::getLink());
        $this->feed->channel->addChild('description', $this->getDescription());

        $atomLink = $this->feed->channel->addChild('atom:link', namespace: 'http://www.w3.org/2005/Atom');
        $atomLink->addAttribute('href', static::getLink().'/'.static::getDefaultOutputFilename());
        $atomLink->addAttribute('rel', 'self');
        $atomLink->addAttribute('type', 'application/rss+xml');

        $this->addAdditionalChannelData();
    }

    protected function addAdditionalChannelData(): void
    {
        $this->feed->channel->addChild('language', config('hyde.language', 'en'));
        $this->feed->channel->addChild('generator', 'HydePHP '.Hyde::version());
        $this->feed->channel->addChild('lastBuildDate', date(DATE_RSS));
    }

    protected function getDescription(): string
    {
        return static::xmlEscape(
            config(
                'hyde.rss_description',
                static::getTitle().' RSS Feed'
            )
        );
    }

    protected static function xmlEscape(string $string): string
    {
        return htmlspecialchars($string, ENT_XML1 | ENT_COMPAT, 'UTF-8');
    }

    public static function getTitle(): string
    {
        return static::xmlEscape(
            config('site.name', 'HydePHP')
        );
    }

    public static function getLink(): string
    {
        return static::xmlEscape(
            rtrim(
                config('hyde.site_url') ?? 'http://localhost',
                '/'
            )
        );
    }

    public static function getDefaultOutputFilename(): string
    {
        return config('hyde.rss_filename', 'feed.xml');
    }

    public static function generateFeed(): string
    {
        return (new static)->generate()->getXML();
    }

    public static function canGenerateFeed(): bool
    {
        return (Hyde::uriPath() !== false)
            && config('hyde.generate_rss_feed', true)
            && Features::hasBlogPosts()
            && extension_loaded('simplexml');
    }
}
