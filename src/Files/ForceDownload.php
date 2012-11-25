<?php

/**
 * Used to create views which force download of specific files
 *
 * You can add additional properties on the fly
 * @author paul.visco@roswellpark.org
 * @package Files
 */
namespace sb\Files;

class ForceDownload
{
    /**
     * Send headers and begins force-download
     *
     * @param string $file The path to the file to force download
     * @param strin $display_file_name The filename to give to the 
     * force download if different than the basename of the file arg
     *
     */
    public static function send($file, $display_file_name = '')
    {
        $display_file_name = $display_file_name ? $display_file_name : basename($file);
        $display_file_name = str_replace(" ", "_", $display_file_name);
        header("HTTP/1.1 200 OK");
        header("Status: 200 OK");
        header("Pragma: private");
        header("Expires: 0");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Content-Transfer-Encoding: Binary");
        header('Content-Type: application/force-download');
        header('Content-disposition: attachment; filename="'.$display_file_name.'"');

        while (ob_get_level() > 0) {
            ob_end_flush();
        }
        \sb\Files::read_chunked($file);
    }

    /**
     * Converts a file or directory into a zip file for consumption by the browser
     * @param string $path The path to the file or directory
     * @return string 
     */
    public static function fileToZip($path)
    {
        if (is_file($path) || is_dir($path)) {
            $zip = new \ZipArchive;
            $zip_file = ROOT . '/private/cache/zip/' . md5(microtime(true));
            if (!is_dir(dirname($zip_file))) {
                mkdir(dirname($zip_file), 0775, true);
            }

            if ($zip->open($zip_file, \ZipArchive::CREATE) === true) {
                if (is_dir($path)) {
                    $iterator = new \DirectoryIterator($path);

                    foreach ($iterator as $file) {
                        if ($file->isFile()) {
                            $bn = $file->getBasename();
                            $zip->addFile($file->getPath() . '/' . $bn, $bn);
                        }
                    }
                } else {
                    $zip->addFile($path, basename($path));
                }

                if ($zip->close()) {
                    self::send($zip_file, str_replace("/", "_", basename($path)) . '.zip');
                    unlink($zip_file);
                }
            } else {
                throw(new \Exception('failed to create zip file'));
            }
        } else {
            throw(new \Exception('No data found!'));
        }
    }
}

