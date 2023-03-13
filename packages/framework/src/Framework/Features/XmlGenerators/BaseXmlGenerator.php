<?php

/** @noinspection PhpComposerExtensionStubsInspection */

declare(strict_types=1);

namespace Hyde\Framework\Features\XmlGenerators;

use Exception;
use SimpleXMLElement;

use function extension_loaded;
use function htmlspecialchars;
use function throw_unless;

/**
 * Defines the public API for XML generators and provides a shared codebase for common helpers.
 *
 * @see \Hyde\Framework\Features\XmlGenerators\RssFeedGenerator
 * @see \Hyde\Framework\Features\XmlGenerators\SitemapGenerator
 */
abstract class BaseXmlGenerator
{
    protected SimpleXMLElement $xmlElement;

    /**
     * Generate the XML document.
     *
     * @return $this
     */
    abstract public function generate(): static;

    abstract protected function constructBaseElement(): void;

    /**
     * Generate a new XML document and get the contents as a string.
     */
    public static function make(): string
    {
        return (new static)->generate()->getXML();
    }

    /**
     * Create a new XML generator instance.
     */
    public function __construct()
    {
        throw_unless(extension_loaded('simplexml'),
            new Exception('The SimpleXML extension is required to generate RSS feeds and sitemaps.')
        );

        $this->constructBaseElement();
    }

    /**
     * Get the XML document as a string.
     */
    public function getXml(): string
    {
        return (string) $this->xmlElement->asXML();
    }

    /**
     * Get the XML document as a SimpleXMLElement object.
     */
    public function getXmlElement(): SimpleXMLElement
    {
        return $this->xmlElement;
    }

    protected function escape(string $string): string
    {
        return htmlspecialchars($string, ENT_XML1 | ENT_COMPAT, 'UTF-8');
    }

    protected function addChild(SimpleXMLElement $element, string $name, string $value): SimpleXMLElement
    {
        return $element->addChild($name, $this->escape($value));
    }
}
