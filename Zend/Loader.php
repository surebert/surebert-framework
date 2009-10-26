<?php
/**
 * Used to load the Zend framework and enable autoload
 * @version 1.0 04-28-2009 04-28-2009
 * @package sb_Zend
 */

class sb_Zend_Loader{

    public function __construct($path){
        
        //checks to make sure this didn't already happen
        if(!class_exists('Zend_Loader')){
            //add Zend framework to the include path otherwise, internal requires won't work
            set_include_path($path . PATH_SEPARATOR . get_include_path());

            //load the zf autoloader
            require($path.'/Zend/Loader.php');
            Zend_Loader::registerAutoload();

        }
    }

}
?>