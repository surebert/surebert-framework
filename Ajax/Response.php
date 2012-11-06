<?php

/**
 * Used to model and standardize an Ajax Response
 *
 * @author paul.visco@roswellpark.org
 * @package sb_Ajax
 */
namespace sb\Ajax;

class Response
{

    /**
     * The body of the response to be sent back to the browser
     *
     * @var string
     */
    public $content = null;

    /**
     * The callback javascript function to wrap the response in
     * @var string
     */
    public $callback = null;

    /**
     * An array of headers to be passed back to the browser.
     *
     * @var array
     */
    private $headers = Array(
        'Content-Type' => 'text/html'
    );

    /**
     * Sets up a ajax repsonse for dispatch
     *
     *
     * <code>
     * $response = new \sb\Ajax\Response(new person());
     * $response->dispatch();
     * </code>
     *
     * @param mixed object/xml/string/boolean $content
     */
    public function __construct($content = null)
    {

        if (!\is_null($content)) {
            $this->set_content($content);
        }

        if (isset(Gateway::$request->get['sb_callback'])) {
            $this->callback = Gateway::$request->get['sb_callback'];
        }
    }

    /**
     * Sets the content type header passed to the browser
     *
     * @param string $content_type e.g application/xml
     */
    public function setContentType($content_type = 'text/html')
    {
        $this->headers['Content-Type'] = $content_type;
    }

    /**
     * Sets the content to send back to the browser and sets the content type based on the type of content passed
     *
     * @param mixed object/bool/xml/string $content
     *
     * @todo if someone wants to bother with xml add it, and if so what
     * determines if it is xml, an xml object, a string detected, etc
     */
    public function setContent($content)
    {

        if (is_bool($content)) {

            $this->setContentType('boolean/value');
            $this->content = $content ? 1: 0;

        } elseif (is_object($content) || is_array($content)) {

            $this->setContentType('application/json');
            $this->content = json_encode($content);

        } else {
            $this->setContentType('text/html');
            $this->content = $content;
        }

    }

    /**
     * Adds a custom header to be passed to the browser
     *
     * @param string $key
     * @param string $val
     */
    public function addCustomHeader($key, $val)
    {
        $this->headers[$key] = $val;
    }

    /**
     * Echos the response to the browser.
     *
     */
    public function dispatch()
    {

        foreach ($this->headers as $header => $val) {
            header($header.': '.$val);
        }

        //wrap in callback if set
        if (!is_null($this->callback)) {
            echo $this->callback.'('.$this->content.');';
        } else {
            echo $this->content;
        }

    }
}

