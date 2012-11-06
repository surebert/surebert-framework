<?php

/**
 * Used to model an epub OPF
 * @author paul.visco@roswellpark.org
 * @package Epub
 */
namespace sb\Ebook\Epub;

class OPF extends \DOMDocument
{

    /**
     *
     * @var DOMElement
     */
    public $package;

    /**
     *
     * @var DOMElement
     */
    public $metadata;

    /**
     *
     * @var DOMElement
     */
    public $manifest;

    /**
     * @var DOMElement
     */
    public $spine;

    public $formatOutput = true;

    private $language = null;

    private $date = null;

    protected $date_format = 'Y-m-d\TH:i:s.000000P';

    public function __construct($version = '1.0', $encoding = 'UTF-8')
    {
        parent::__construct($version, $encoding);

        $this->createPackage();
        $this->createMetadata();
        $this->createManifest();
        $this->createSpine();
    }

    public function createPackage()
    {
        $this->package = $this->appendChild($this->createElement('package'));
        $this->package->setAttribute('xmlns', 'http://www.idpf.org/2007/opf');
        //TODO get book identifier
        $this->package->setAttribute('unique-identifier', 'BookID');
        $this->package->setAttribute('version', '2.0');
    }

    public function createMetadata()
    {
        $this->metadata = $this->package->appendChild($this->createElement('metadata'));
        $this->metadata->setAttribute('xmlns:dc', 'http://purl.org/dc/elements/1.1/');
        //$this->metadata->setAttribute('xmlns:xsi', 'http://www.w3.org/2001/XMLSchema-instance');
        $this->metadata->setAttribute('xmlns:opf', 'http://www.w3.org/2001/XMLSchema-instance');
        //$this->metadata->setAttribute('xmlns:dcterms', 'http://purl.org/dc/terms/');
    }

    public function createManifest()
    {
        $this->manifest = $this->package->appendChild($this->createElement('manifest'));
        $this->addFile('book.ncx', 'application/x-dtbncx+xml', 'ncx');
    }

    public function createSpine()
    {
        $this->spine = $this->package->appendChild($this->createElement('spine'));
        $this->spine->setAttribute('toc', 'ncx');
    }

    /**
     * The item id e.g. a chapter id
     * @param string $idref
     * @param string $linear
     */
    public function addToSpine($idref, $linear = 'yes')
    {
        $itemref = $this->spine->appendChild($this->createElement('itemref'));
        $itemref->setAttribute('idref', $idref);
        $itemref->setAttribute('linear', 'yes');

        return $itemref;
    }

    public function setTitle($title)
    {
        $this->title = $this->metadata->appendChild($this->createElement('dc:title'));
        $txt = $this->createTextNode($title);
        $this->title->appendChild($txt);

        return $txt;
    }

    public function setDescription($description)
    {
        $this->description = $this->metadata->appendChild($this->createElement('dc:description'));
        $txt = $this->createTextNode($description);
        $this->description->appendChild($txt);

        return $txt;
        //TODO allow reset of description
        //$this->description->removeChild($this->description->childNodes[0]);
    }

    /**
     *
     * @param $language
     */
    public function setLanguage($language = 'en')
    {
        if (mb_strlen($language) != 2) {
            throw(new \Exception("language must be two char language code e.g. en, de"));
        }
        $this->language = $this->metadata->appendChild($this->createElement('dc:language'));
        $txt = $this->createTextNode($language);
        $this->language->appendChild($txt);

        return $txt;
    }

    public function setPublisher($publisher)
    {
        $this->publisher = $this->metadata->appendChild($this->createElement('dc:publisher'));
        $txt = $this->createTextNode($publisher);
        $this->publisher->appendChild($txt);

        return $txt;
    }

    //publisher URL
    public function setRelation($relation)
    {
        $this->relation = $this->metadata->appendChild($this->createElement('dc:relation'));
        $txt = $this->createTextNode($relation);
        $this->relation->appendChild($txt);

        return $txt;
    }

    public function addFile($href, $media_type, $file_id)
    {
        $item = $this->manifest->appendChild($this->createElement('item'));
        $item->setAttribute('id', $file_id ? : md5(microtime(true)));
        $item->setAttribute('href', $href);
        $item->setAttribute('media-type', $media_type);

        return $item;
    }

    public function setIdentifier($identifier, $identifier_type)
    {
        $this->identifier = $this->metadata->appendChild($this->createElement('dc:language'));

        $this->metadata->setAttribute('opf:scheme', $identifier_type);
        //TODO get real book id
        $this->metadata->setAttribute('id', 'BookId');
        $this->identifier->appendChild($this->createTextNode($identifier));
    }

    /**
     * Book author or creator, optional.
     * .
     * @param string $author   Used for the dc:creator metadata parameter in the 
     * OPF file
     * @param string $sort_key is basically how the name is to be sorted, usually
     *  it's "Lastname, First names" where the $author is the straight
     *  "Firstnames Lastname"
     */
    public function setAuthor($author, $sort_key = '')
    {
        $this->creator = $this->metadata->appendChild($this->createElement('dc:creator'));
        $this->creator->setAttribute('opf:role', "aut");
        $txt = $this->createTextNode($author);
        $this->creator->appendChild($txt);
        if ($sort_key) {
            $this->creator->setAttribute('opf:file-as', $sort_key);
        }
    }

    public function setSource($url)
    {
        $this->source = $this->metadata->appendChild($this->createElement('dc:source'));
        $txt = $this->createTextNode($url);
        $this->source->appendChild($txt);

        return $txt;
    }

    public function setRights($rights)
    {
        $this->rights = $this->metadata->appendChild($this->createElement('dc:rights'));
        $txt = $this->createTextNode($url);
        $this->rights->appendChild($txt);

        return $txt;
    }

    public function setDate($date = null)
    {
        $date = $date ? : time();

        $this->date = $this->metadata->appendChild($this->createElement('dc:date'));
        $txt = $this->createTextNode(date($this->date_format, $date));
        $this->date->appendChild($txt);

        return $txt;
    }

    public function saveXml($node = null, $options = null)
    {
        if (!$this->language) {
            $this->setLanguage('en');
        }

        if (!$this->date) {
            $this->setDate();
        }

        return parent::saveXML($node, $options);
    }
}

