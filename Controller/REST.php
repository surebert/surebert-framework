<?php

/**
 * Used to process REST requests, methods must have @servable true to be accessible
 * @author paul.visco@roswellpark.org
 * @package Controller
 */
namespace sb\Controller;

class REST extends HTTP
{

    /**
     * An incomming put request handler
     */
    public function put()
    {

    }

    /**
     * An incomming post request handler
     */
    public function post()
    {

    }

    /**
     * An incomming get request handler
     */
    public function get()
    {

    }

    /**
     * An incomming head request handler
     */
    public function head()
    {

    }

    /**
     * An incomming delete request handler
     */
    public function delete()
    {

    }

    /**
     * An incomming options request handler
     */
    public function options()
    {

    }

    /**
     * Used to render the output through the filter_output method by calling the
     * handler appropriate to the HTTP request
     * @return string
     */
    public function render()
    {
        if ($this->onBeforeRender() === false) {
            return $this->notFound();
        }

        $method = Gateway::$request_method;
        if (method_exists($this, $method)) {
            $reflection = new \ReflectionMethod($this, $method);

            //check for phpdocs
            $docs = $reflection->getDocComment();
            $servable = false;
            if (!empty($docs)) {
                if (preg_match("~@servable (true|false)~", $docs, $match)) {
                    $servable = $match[1] == 'true' ? true : false;
                }
            }

            if ($servable) {
                return $this->filter_output($this->$method());
            } else {
                return $this->filter_output($this->notFound($method));
            }
        } else {
            return $this->filter_output($this->notFound($method));
        }
    }
}

