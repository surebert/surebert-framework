<?php

/**
 * Used to handle http requests
 * 
 * @author paul.visco@roswellpark.org
 * @package Controller
 */
namespace sb\Controller;

class HTTP extends Base
{

    /**
     * Sets a session var
     * 
     * @param string $name The key of the session
     * @param string $value The value of the session
     */
    public function setSession($key, $value)
    {
        $_SESSION[$key] = $value;
    }

    /**
     * Sets a session var
     * 
     * @param string $key The key of the session
     */
    public function unsetSession($key)
    {
        if (isset($_SESSION[$key])) {
            unset($_SESSION[$key]);
        }
    }

    /**
     * Set an http cookie
     * 
     * @param string $name The name of the cookie
     * @param string $value The value of the cookie
     * @param integer $expire The time the cookie expires. This is a Unix timestamp
     *  so is in number of seconds since the epoch. In other words, you'll most 
     * likely set this with the time() function plus the number of seconds 
     * before you want it to expire
     * @param string $path The path on the server in which the cookie will be 
     * available on. If set to '/', the cookie will be available within the 
     * entire domain. If set to '/foo/', the cookie will only be available within
     *  the /foo/ directory and all sub-directories such as /foo/bar/ of domain.
     * The default is /
     * @param string $domain The domain that the cookie is available to. To make
     *  the cookie available on all subdomains of example.com 
     * (including example.com itself) then you'd set it to '.example.com'.
     *  Although
     * @param boolean $secure Indicates that the cookie should only be transmitted
     *  over a secure HTTPS connection from the client. When set to TRUE, the 
     * cookie will only be set if a secure connection exists.
     * @param boolean $httponly When TRUE the cookie will be made accessible only
     *  through the HTTP protocol. This means that the cookie won't be 
     * accessible by scripting languages, such as JavaScript. 
     */
    public function setCookie(
        $name,
        $value = '',
        $expire = 0,
        $path = '/',
        $domain = '',
        $secure = false,
        $httponly = false
    ) {
        setcookie($name, $value, $expire, $path, $domain, $secure, $httponly);
    }

    /**
     * Unsets a cookie value by setting it to expire
     * @param string $name The cookie name 
     * @param string path The path to clear, defaults to /
     */
    public function unsetCookie($name, $path = '/')
    {
        setcookie($name, '', time() - 86400, '/', '', 0);
        if (isset($_COOKIE) && isset($_COOKIE[$name])) {
            unset($_COOKIE[$name]);
        }
        if (isset($this->request->cookie[$name])) {
            unset($this->request->cookie[$name]);
        }
    }

    /**
     * Sends a content type header
     * @param integer $type The content type e.g. image/jpeg audio/mpeg3 text/plain
     */
    public function setContentType($type)
    {
        $this->sendHeader('Content-Type', $type);
    }

    /**
     * Sends an http header
     * @param string $header The header to send e.g. Content-Type
     * @param string $value  The value to send e.g. text/plain.  If a value is 
     * set then a colon+space is added between header and value
     */
    public function sendHeader($header, $value = '')
    {
        if (!empty($value)) {
            $header .= ': ' . $value;
        }
        header($header);
    }

    /**
     * Send an http error header
     * @param integer $error_num The number of the error as found http://www.w3.org/Protocols/rfc2616/rfc2616-sec10.html
     * @param string $err_str The error string to send, defaults sent for
     * 400, 401, 403, 404, 405, 410, 415, 500, 501 unless specified
     */
    public function sendError($error_num, $err_str = '')
    {

        if (empty($err_str)) {
            switch ($error_num) {
                case 400:
                    $err_str = 'Bad Request';
                    break;

                case 401:
                    $err_str = 'Unauthorized';
                    break;

                case 403:
                    $err_str = 'Forbidden';
                    break;

                case 404:
                    $err_str = 'Not Found';
                    break;

                case 405:
                    $err_str = 'Method Not Allowed';
                    break;

                case 410:
                    $err_str = 'Gone';
                    break;

                case 415:
                    $err_str = 'Unsupported Media Type';
                    break;

                case 500:
                    $err_str = 'Internal Server Error';
                    break;

                case 501:
                    $err_str = 'Not Implemented';
                    break;
            }
        }
        $this->sendHeader("HTTP/1.0 $error_num $err_str");
    }

    /**
     * The url to redirect to and the type 301, 302, 307, etc
     * @param type $url The URL to redirect to
     * @param type $type defaults to 302.  Browsers typically re-request a 307 
     * page every time, cache a 302 page for the session, and cache a 301 page 
     * for longer, or even indefinitely.  Search engines typically transfer 
     * "page rank" to the new location for 301 redirects, but not for 302, 303 
     * or 307.
     * 301 Moved Permanently
     * 302 Found
     * 303 See Other
     * 307 Temporary Redirect
     */
    public function sendRedirect($url, $type = 302)
    {
        header("Location: $url", true, $type);
    }

    /**
     * Gets a get variable value or returns the default value (null unless overridden)
     * @param string $key The $_GET var key to look for
     * @param mixed $default_val null by default
     * @return mixed string value or null 
     */
    public function getGet($key, $default_val = null)
    {
        return $this->request->getGet($key, $default_val);
    }

    /**
     * Gets a post variable value or returns the default value (null unless overridden)
     * @param string $key The $_POST var key to look for
     * @param mixed $default_val null by default
     * @return mixed string value or null 
     */
    public function getPost($key, $default_val = null)
    {
        return $this->request->getPost($key, $default_val);
    }

    /**
     * Gets a cookie value if set, otherwise returns null
     * 
     * @param string $key The key to look for
     * @return mixed the string value or null if not found
     */
    public function getCookie($key, $default_val = null)
    {
        return $this->request->getCookie($key, $default_val);
    }

    /**
     * Gets a get variable value or returns the default value (null unless overridden)
     * @param string $key The $_SESSION var key to look for
     * @param mixed $default_val null by default
     * @return mixed string value or null 
     */
    public function getSession($key, $default_val = null)
    {
        return $this->request->getSession($key, $default_val);
    }

    /**
     * Gets a args variable value or returns the default value (null unless overridden)
     * @param integer $arg_num The numeric arg value
     * @param mixed $default_val null by default
     * @return mixed string value or null 
     */
    public function getArg($arg_num, $default_val = null)
    {
        return $this->request->getArg($arg_num, $default_val);
    }

    /**
     * Added option for requesting basic auth.  ONLY USE OVER SSL
     * @param callable $check_auth  the callable that determines success or not
     * @param string $realm the realm beings used
     * @return boolean  
     */
    public function requireBasicAuth($check_auth = '', $realm = 'Please enter your username and password')
    {

        $authorized = false;
        if (!isset($_SERVER['PHP_AUTH_USER'])) {
            header('WWW-Authenticate: Basic realm="' . $realm . '"');
            header('HTTP/1.0 401 Unauthorized');
            echo 'You must authenticate to continue';
        } else {

            if (is_callable($check_auth)) {
                $authorized = $check_auth($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW']);
            }

            if (!$authorized) {
                session_unset();
                unset($_SERVER['PHP_AUTH_USER']);
                return $this->requireBasicAuth($check_auth, $realm);
            }
        }

        return $authorized;
    }
}

