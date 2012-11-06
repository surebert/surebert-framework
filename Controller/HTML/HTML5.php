<?php

/**
 * A ControllerClass that all HTML Controllers could/should extend
 * @author paul.visco@roswellpark.org
 * @package Controller
 */
namespace sb\Controller\HTML;

use \sb\Controller\HTTP;

class HTML5 extends HTTP
{

    /**
     * Assigns the sb_HTML_HeadMeta property
     */
    public function __construct()
    {
        $this->meta = new HeadMeta();
    }

    /**
     * The doc type of the HTML page
     *
     * @var string
     */
    public $doc_type = '<!DOCTYPE html>';

    /**
     * The opening HTML tag
     * @var string
     */
    public $opening_html_tag = '<html>';

    /**
     * An example property - it is used to create the HTML header, any view that extends this one
     * can use this property or override it
     * @var string
     */
    public $title = 'Untitled';

    /**
     * The meta tags for the HTML head - author, description, keywords
     * You can add additional properties on the fly, they will be rendered in the $this->htmlHead() method
     * @var \sb\HTML_HeadMeta
     */
    public $meta;

    /**
     * The CSS style tags for the page
     * @var array
     */
    public $styles = Array('app.css');

    /**
     * Additional <head> markup that you want appended before </head>
     * @var string
     */
    public $custom_head_markup = '';

    /**
     * The content type of the document expressed as e.g. UTF-8, ISO-8859-1, etc
     * @var string
     */
    public $charset = 'UTF-8';

    /**
     * Creates a javascript include tag
     * @param $scripts array/string The file names or an array of file names to include
     */
    public function includeJavascript($scripts)
    {

        $src = (!is_array($scripts)) ? Array($scripts) : $scripts;
        $html = '';
        foreach ($scripts as $s) {
            if (!strstr($s, '/')) {
                $s = '/js/' . $s;
            }
            $html .= "\n" . '<script type="text/javascript" src="' . $s . '"></script>';
        }

        return $html;
    }

    /**
     * Renders the HTML head
     * @param $custom_head_markup string A string of data to include in the HTML head, right before </head>
     */
    public function htmlHead($custom_head_markup = '')
    {

        if (!empty($custom_head_markup)) {
            $this->custom_head_markup = $custom_head_markup;
        }

        $html = $this->doc_type . "\n";
        $html .= $this->opening_html_tag . "\n";
        $html .= '<head>' . "\n";
        $html .= '<meta http-equiv="Content-Type" content="text/html; charset=' . $this->charset . '" />' . "\n";

        $html .= '<title>' . $this->title . '</title>' . "\n";

        if ($this->meta instanceof HeadMeta) {

            foreach (get_object_vars($this->meta) as $key => $val) {
                $html .= '<meta name="' . $key . '" content="' . $val . '" />' . "\n";
            }
        }

        $html .= '<style type="text/css">';

        foreach ($this->styles as $style) {
            if (!preg_match("~^(http|/)~", $style)) {
                $style = '/css/' . $style;
            }
            $html .= "@import '" . $style . "';\n";
        }
        $html .= "</style>\n";

        if (!empty($this->custom_head_markup)) {
            $html.= $this->custom_head_markup;
        }

        $html.= "</head>\n";
        return $html;
    }
}

