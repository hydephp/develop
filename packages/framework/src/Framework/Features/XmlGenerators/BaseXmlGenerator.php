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

    abstract protected function constructBaseElement(): void;

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

    public function getXml(): string
    {
        return (string) $this->xmlElement->asXML();
    }

    public function getXmlElement(): SimpleXMLElement
    {
        return $this->xmlElement;
    }

    protected function escape(string $string): string
    {
        return htmlspecialchars($string, ENT_XML1 | ENT_COMPAT, 'UTF-8');
    }
}
