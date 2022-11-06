<?php

/** @noinspection PhpComposerExtensionStubsInspection */
/** @noinspection XmlUnusedNamespaceDeclaration */

declare(strict_types=1);

namespace Hyde\Framework\Features\XmlGenerators;

use Exception;
use Hyde\Facades\Site;
use Hyde\Hyde;
use Hyde\Pages\MarkdownPost;
use SimpleXMLElement;
use function config;
use function date;
use function extension_loaded;
use function str_ends_with;
use function throw_unless;

/**
 * @see \Hyde\Framework\Testing\Feature\Services\RssFeedServiceTest
 * @see https://validator.w3.org/feed/docs/rss2.html
 */
class RssFeedGenerator extends BaseXmlGenerator
{
    public function __construct()
    {
        throw_unless(extension_loaded('simplexml'),
            new Exception('The ext-simplexml extension is not installed, but is required to generate RSS feeds.')
        );

        $this->xmlElement = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?>
            <rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom" xmlns:dc="http://purl.org/dc/elements/1.1/" />');
        $this->xmlElement->addChild('channel');

        $this->addBaseChannelItems();
    }

    public function generate(): static
    {
        MarkdownPost::getLatestPosts()
            ->each(fn (MarkdownPost $post) => $this->addItem($post));

        return $this;
    }

    protected function addItem(MarkdownPost $post): void
    {
        $item = $this->xmlElement->channel->addChild('item');
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
        $this->xmlElement->channel->addChild('title', $this->escape(Site::name()));
        $this->xmlElement->channel->addChild('link', $this->escape(Site::url()));
        $this->xmlElement->channel->addChild('description', $this->getDescription());

        $this->xmlElement->channel->addChild('language', config('site.language', 'en'));
        $this->xmlElement->channel->addChild('generator', 'HydePHP '.Hyde::version());
        $this->xmlElement->channel->addChild('lastBuildDate', date(DATE_RSS));

        $atomLink = $this->xmlElement->channel->addChild('atom:link', namespace: 'http://www.w3.org/2005/Atom');
        $atomLink->addAttribute('href', $this->escape(Hyde::url($this->getFilename())));
        $atomLink->addAttribute('rel', 'self');
        $atomLink->addAttribute('type', 'application/rss+xml');
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

    public static function getFilename(): string
    {
        return config('hyde.rss_filename', 'feed.xml');
    }

    public static function getDescription(): string
    {
        return static::escape(config(
            'hyde.rss_description',
            static::escape(Site::name()).' RSS Feed'
        ));
    }
}
