<?php

/**
 * Used to model and Epub Chapter
 * . "<html xmlns=\"http://www.w3.org/1999/xhtml\">\n"
  . "<head>"
  . "<meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\" />\n"
  . "<link rel=\"stylesheet\" type=\"text/css\" href=\"styles.css\" />\n"
  . "<title>Test Book</title>\n"
  . "</head>\n"
  . "<body>\n";
 * @author paul.visco@roswellpark.org
 * @package Ebook
 */
namespace sb\Ebook\Epub;

class Chapter
{

    /**
     *
     * @var DOMDocument
     */
    public $html;

    public $head;

    public $body;

    public $title;

    public function __construct($title, $html = '', $css = '')
    {
        $this->title = $title;

        $this->xml = new \DomDocument('1.0', 'UTF-8');

        $this->createHtml();
        $this->createHead($title);
        $this->createBody();
        if (!empty($html)) {
            $this->setBodyInnerHtml($html);
        }
        $this->addCss($css);
    }

    protected function createHtml()
    {
        $this->html = $this->xml->appendChild($this->xml->createElement('html'));
        $this->html->setAttribute('xmlns', 'http://www.w3.org/1999/xhtml');
    }

    protected function createHead($title_txt)
    {
        $this->head = $this->html->appendChild($this->xml->createElement('head'));
        $title = $this->head->appendChild($this->xml->createElement('title'));
        $title->appendChild($this->xml->createTextNode($title_txt));
    }

    protected function createBody()
    {
        $this->body = $this->html->appendChild($this->xml->createElement('body'));
    }

    public function addCss($href = '')
    {

        if ($href) {
            if (is_string($href)) {
                $href = Array($href);
            }

            if (is_array($href)) {
                foreach ($href as $h) {

                    $link = $this->head->appendChild($this->xml->createElement('link'));
                    $link->setAttribute('rel', 'stylesheet');
                    $link->setAttribute('type', 'text/css');
                    $link->setAttribute('href', $h);
                }
            }
        }
    }

    public function setBodyInnerHtml($html)
    {
        $fragment = $this->xml->createDocumentFragment();
        $fragment->appendXML($html);
        $this->body->appendChild($fragment);
    }

    public function saveXml()
    {
        return $this->xml->saveXML();
    }
}

