<?php
/**
 * Used to convert App Logger logs to camel case when run in /private/logs
 * 
 * You should make a backup of logs first just in case
 * 
 * @author paul.visco@roswellpark.org
 * 
 * <code>
 * php camel_case_logs.php
 * </code>
 * 
 */

/**
 * Converts underscore string to camel case
 * @param string $str
 * @return string
 */
function toCamelCase($str) {
    return preg_replace_callback('/_([A-z])/', function($v) {
                return strtoupper($v[1]);
            }, $str);
}

//grab alll the items in the directory
$items = glob("*");
foreach ($items as $item) {
    //rename the directories
    if (is_dir(__DIR__ . '/' . $item)) {
        rename(__DIR__ . '/' . $item, __DIR__ . '/' . toCamelCase($item));
    }
}
