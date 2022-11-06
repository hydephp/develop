<?php

/** @noinspection PhpComposerExtensionStubsInspection */

declare(strict_types=1);

namespace Hyde\Framework\Features\XmlGenerators;

use Exception;
use SimpleXMLElement;
use function extension_loaded;
use function htmlspecialchars;
use function throw_unless;

abstract class BaseXmlGenerator implements XmlGeneratorContract
{
    protected SimpleXMLElement $xmlElement;

    public static function make(): string
    {
        return (new static)->generate()->getXML();
    }

    public function __construct()
    {
        throw_unless(extension_loaded('simplexml'),
            new Exception('The SimpleXML extension is required to generate RSS feeds and sitemaps.')
        );

        $this->constructBaseElement();
    }

    protected static function escape(string $string): string
    {
        return htmlspecialchars($string, ENT_XML1 | ENT_COMPAT, 'UTF-8');
    }

    public function getXML(): string
    {
        return (string) $this->xmlElement->asXML();
    }

    /**
     * @return \SimpleXMLElement
     */
    public function getXmlElement(): SimpleXMLElement
    {
        return $this->xmlElement;
    }

    abstract protected function constructBaseElement();
}
