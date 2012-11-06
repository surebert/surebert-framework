<?php

/**
 * An ControllerClass that all HTML views could/should extend
 * @author paul.visco@roswellpark.org
 * @package Controller
 */
namespace sb\Controller\HTML;

class XHTML extends HTML5
{

    /**
     * The doc type of the HTML page
     *
     * @var string
     */
    public $doc_type = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" 
        "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">';

    /**
     * The opening HTML tag
     * @var type 
     */
    public $opening_html_tag = '<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">';
}

