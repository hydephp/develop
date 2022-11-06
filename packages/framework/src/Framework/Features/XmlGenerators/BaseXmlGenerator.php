<?php

/** @noinspection PhpComposerExtensionStubsInspection */

declare(strict_types=1);

namespace Hyde\Framework\Features\XmlGenerators;

use SimpleXMLElement;
use function htmlspecialchars;

abstract class BaseXmlGenerator implements XmlGeneratorContract
{
    protected SimpleXMLElement $xmlElement;

    public static function make(): string
    {
        return (new static)->generate()->getXML();
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
}
