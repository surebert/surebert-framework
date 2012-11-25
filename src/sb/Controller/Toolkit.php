<?php

/**
 * Creates concatenated javascript files for the surebert toolkit from the arguments it is fed
 * @author paul.visco@roswellpark.org
 * @package Controller
 */
namespace sb\Controller;

class Toolkit extends Base
{

    public $input_args_delimiter = ',';

    /**
     * Determines if caching is used
     * @var boolean
     */
    public $cache_enable = false;

    /**
     * The base files included with all custom or surebert views, the default is sb for sb.js
     * @var string
     */
    protected $default_files = Array();

    /**
     * The toolkit tag to use for serving the js from surebert
     * @var string
     */
    public $toolkit_root = '/var/www/sbf/toolkit/';

    /**
     * The version of surebert being served
     * @var string
     */
    public $version;

    /**
     * Removes comments from javascript
     * @author paul.visco@roswellpark.org
     */
    public function filterOutput($output)
    {
        if (!isset($this->request->get['sb_comments'])) {
            return preg_replace("~/\*\*.*?\*/~s", "", $output);
        } else {
            return $output;
        }
    }

    /**
     * Concatenates the javascript/surebert files and turns them into cache if caching is enable
     * @param $files
     */
    protected function concatFiles($files = Array(), $version = '')
    {

        if ($files[0] == 'sb' && strstr(\sb\Gateway::$agent, 'MSIE')) {

            array_unshift($files, 'js1_5');
        }

        $root = false;
        if (empty($version)) {
            $root = SUREBERT_TOOLKIT_PATH;
        } elseif (is_numeric($version)) {
            $root = $this->toolkit_root . '/tags/' . $version;
        } else {
            $root = $this->toolkit_root . '/' . $version;
        }

        if (!is_dir($root)) {
            $root = $this->toolkit_root . 'trunk';
        }

        $this->version = basename($root);

        $binary = preg_match("~\.(swf|gif|png)$~", $files[0], $match);

        if ($binary) {
            if ($match[1] == 'swf') {
                header("Content-type: application/x-shockwave-flash");
            } elseif ($match[1] == 'gif' || $match[1] == 'png') {
                header("Content-type: image/" . $match[1]);
            }
        } else {
            $this->addJavascriptHeaders();
            echo '//v ' . $this->version . ' - ' . date('m/d/Y H:i:s') . "\n";
        }

        if ($this->cache_enable) {
            $cache = isset(\App::$cache) ? \App::$cache : new Cache_FileSystem();
            $key = '/toolkit/' . md5(implode(",", $files) . $version);

            $data = $cache->fetch($key);
            if ($data) {
                echo $data;
                return true;
            }
        }

        $surebert = $this->default_files;
        $this->loaded_files = Array();
        foreach ($files as $file) {
            if ($binary) {
                $surebert[] = $file;
            } else {

                $surebert[] = str_replace('.', '/', $file) . '.js';
            }
        }
        ob_start();

        foreach ($surebert as $file) {
            echo $this->grabFile($file, $root);
        }

        $js = ob_get_clean();

        if (isset($this->request->get['manifest'])) {
            $m = $this->request->get['manifest'];
            if ($m == 'js') {
                return json_encode($this->loaded_files);
            } else {
                return print_r($this->loaded_files, 1);
            }
        }
        if ($this->cache_enable) {
            $cache->store($key, $js);
        }

        return $js;
    }

    /**
     * Grabs a file
     * @param string $file The file to load
     * @param string $root The root to load
     * @return string The file data
     */
    protected function grabFile($file, $root)
    {
        $data = '';

        if (is_file($root . '/' . $file)) {

            $this->loaded_files[] = $file;

            $file = $root . '/' . $file;

            $data = file_get_contents($file);
            if (!strstr($file, 'sb.js')) {
                preg_match_all("~sb\.include\([\"'](.*?)[\"']~", $data, $includes);

                if ($includes[1]) {
                    $precludes = '';
                    foreach ($includes[1] as $include) {
                        $include = \str_replace('.', '/', $include) . '.js';
                        if (!\in_array($include, $this->loaded_files)) {
                            $precludes .= $this->grabFile($include, $root);
                        }
                    }

                    $data = $precludes . "\n" . $data;
                }
            }
        } else {

            echo"\nthrow('ERROR: " . $file . " Surebert module \""
            . \basename($file) . "\" could not be located by /surebert/load ');";
        }

        return $data;
    }

    /**
     * Adds javascript headers to the file and adds cache control if this is the main
     * view being displayed.  If it is included in another view do not do this
     * as you don't want HTML being served as javascript
     */
    protected function addJavascriptHeaders()
    {

        if (!$this->included) {
            \header("Content-type: application/x-javascript");
        }
    }

    /**
     * Serves out individual files or sb by default. e.g. /surebert/load or /surebert/load/colors.rand
     * @servable true
     */
    public function load()
    {
        $surebert = $this->request->args;

        if (empty($surebert)) {

            $surebert = Array('sb');
        }

        if ($surebert[0] == 'sb') {
            echo "var sbBase = '/surebert/load/';\n";
        }
        echo $this->concatFiles($surebert);
    }

    /**
     * Serves out the most common surebert toolkit files
     * @servable true
     */
    public function basic()
    {
        if (!isset($this->request->get['noexpire'])) {
            header('Expires: ' . gmdate('D, d M Y H:i:s \G\M\T', time() + 259200));
        }

        header("Content-type: application/x-javascript");
        $version = '';
        if (isset($this->request->get['v'])) {
            if (is_numeric($this->request->get['v'])) {
                $version = $this->request->get['v'];
            }
        }

        /**
         * This file loads multiple surebert toolkit modules and javascript files
         * and puts them all into one large file for faster load times
         */
        $surebert = Array(
            "sb",
            "browser.\$_GET",
            "math.rand",
            "cookies",
            "browser.removeSelection",
            "browser.getScrollPosition",
            "browser.scrollTo",
            "Element.prototype.show",
            "Element.prototype.hide",
            "Element.prototype.toggle",
            "Element.prototype.getDimensions",
            "Element.prototype.mv",
            "Element.prototype.getPosition",
            "Element.prototype.getNextSibling",
            "Element.prototype.getPreviousSibling",
            "Element.prototype.isOrHasParentOfClassName",
            "Element.prototype.containsElement",
            "Element.prototype.isWithin",
            "Element.prototype.getContaining",
            "Element.prototype.cssTransition",
            "css.rules",
            "swf",
            "css.styleSheet",
            "events.observer",
            "events.classListener",
            "events.idListener",
            "widget.notifier",
            "json.rpc2"
        );

        $protocol = isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443 ? 'https' : 'http';


        $str = "if(!sbBase){var sbBase = '" . $protocol . "://" . \sb\Gateway::$http_host . "/surebert/load/';}\n";

        $surebert = \array_merge($surebert, $this->request->args);
        $str .= $this->concatFiles($surebert, $version);
        return $str;
    }
}

