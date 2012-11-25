<?php
/**
 * Various functions for working with files
 * @author paul.visco@roswellpark.org
 * @package Files
 *
 */
namespace sb;

class Files{

    /**
     * read a file into chunks for faster force download and better memory management
     *
     */
    public static function readChunked($file_name,$seekat=0,$return_bytes=true) 
    {

         // how many bytes per chunk
        $chunk_size = 1*(1024*1024);
        $buffer = '';
        $cnt =0;

        $handle = fopen($file_name, 'rb');
        fseek($handle, $seekat);
        if ($handle === false) {
            return false;
        }

        while (!feof($handle)) {

            $buffer = fread($handle, $chunk_size);
            echo $buffer;
            if(ob_get_level()){
                ob_flush();
                flush();
            }

            if ($return_bytes) {
                $cnt += strlen($buffer);
            }
        }

        $status = fclose($handle);

        if ($return_bytes && $status) {
            return $cnt;
        }

        return $status;
    }

    /**
     * Used to convert file extensions to the appropriate mime-type
      * http://www.ltsw.se/knbase/internet/mime.htp
     * @param string $ext e.g. mp3
     * @return mixed boolean/string e.g. audio/mpeg, false if not found
     */
    public static function extensionToMime($ext)
    {

            switch($ext){

                case 'bmp':
                case 'gif':
                case 'jpg':
                case 'jpeg':
                case 'png':
                case 'tif':
                    $ext = ($ext=='jpg') ? 'jpeg' : $ext;
                    $m = 'image/'.$ext;
                    break;

                case 'js':
                case 'json':
                case 'rtf':
                case 'pdf':
                case 'xml':
                    $m = 'application/'.$ext;
                    break;

                case 'html':
                case 'txt':
                case 'css':
                case 'csv':
                case 'php':
                    $ext = $ext == 'txt' ? 'plain' : $ext;
                    $m = 'text/'.$ext;
                    break;

                case 'doc':
                    $m = 'application/msword';
                    break;

                case 'docx':
                    $m = 'application/vnd.openxmlformats-officedocument.wordprocessingml.document';
                    break;

                case 'flv':
                    $m = 'video/x-flv';
                    break;

                case 'gz':
                    $m = 'application/x-gzip';
                    break;

                case 'mp3':
                    $m = 'audio/mpeg';
                    break;

                case 'mp4':
                    $m = 'video/mp4';
                    break;
                
                case 'mid':
                    $m = 'audio/x-midi';
                    break;

                case 'ppt':
                case 'pps':
                case 'ppsx':
                    $m = 'application/vnd.ms-powerpoint';
                    break;

                case 'tar':
                    $m = 'application/x-tar';
                    break;

                case 'wav':
                    $m = 'audio/x-wav';
                    break;

                case 'xls':
                    $m = 'application/vnd.ms-excel';
                    break;
                
                case 'xlsx':
                    $m = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
                    break;

                case 'zip':
                    $m = 'application/x-zip-compressed';
                    break;

                default:
                    $m = false;

            }

            return $m;
    }

    /**
     * Gets mime type from file from finfo ext
     *
     * @param string $file Path to file
     * @return string The mime type from finfo
     */
    public static function fileToMime($file)
    {

        $mime = self::filenameToMime($file);
        if($mime){
            return $mime;
        } elseif(class_exists('finfo') && is_file($file)){
            $finfo = @new finfo(FILEINFO_MIME, "/usr/share/misc/magic");

            if($finfo){
                /* get mime-type for a specific file */
                return $finfo->file($file);
            }
        }

        return false;
    }
    /**
     * Convert a filename to a mime type
     * @param string $filename
     * @return string The mime type of the file
     */
    public static function filenameToMime($filename)
    {
        $arr = explode(".", basename($filename));
        $ext = strtolower(end($arr));
        return self::extensionToMime($ext);
    }

    /**
     * Recursively deletes the files in a diretory
     *
     * @param string $dir The directory path
     * @param boolean $del Should directory itself be deleted upon completion
     * @return boolean
     */
    public static function recursiveDelete($dir, $del=0)
    {
    
         if($dir == '/'){
            die("You cannot delete the root directory");
        }

        $iterator = new RecursiveDirectoryIterator($dir);
        foreach (new RecursiveIteratorIterator($iterator, RecursiveIteratorIterator::CHILD_FIRST) as $file){
          $name = $file->getFilename();
          if ($file->isDir() && $name != '.' && $name != '..') {
             rmdir($file->getPathname());
          } elseif($file->isFile()){
             unlink($file->getPathname());
          }
        }

        if($del ==1){
            rmdir($dir);
        }
    }

    /**
     * Determines the size of directors and returns stats object
     * @param string $path The path to the server
     * @return object
     */
    public static function getDirectorySize($path) 
    {

        $stats = new stdClass();
        $stats->size = 0;
        $stats->file_count = 0;
        $stats->dir_count = 0;
        if ($handle = opendir ($path)) {
            while (false !== ($file = readdir($handle))) {
                $nextpath = $path . '/' . $file;
                if ($file != '.' && $file != '..' && !is_link ($nextpath)) {
                    if (is_dir ($nextpath)) {
                        $stats->dir_count++;
                        $result = self::getDirectorySize($nextpath);
                        $stats->size += $result->size;
                        $stats->file_count += $result->file_count;
                        $stats->dir_count += $result->dir_count;
                    }
                    elseif (is_file ($nextpath)) {
                        $stats->size += filesize ($nextpath);
                        $stats->file_count++;
                    }
                }
            }
        }
        closedir ($handle);
        return $stats;
    }
    
        
    /**
     * Get the files from a directory
     * @param string $dir The directory path
     * @param boolean $get_directories Should subdirectories be listed
     * @return array
     */
    public static function getFiles($dir, $get_directories=false)
    {
        $arr = Array();
        $iterator = new DirectoryIterator($dir);
        
        foreach ($iterator as $file){

          if ($get_directories && $file->isDir() && !$file->isDot() && !preg_match("~\.~", $file)) {
             $arr[$file->getBasename()] = Array(
                 'path' => $file->getPath(),
                 'size' => self::getDirectorySize($file->getRealPath()),
                 'mtime' => $file->getMTime(),
                 'name' => $file->getBaseName());
             
          } elseif (!$get_directories && $file->isFile()){
              $arr[] = $file->getBasename();
            }
        }
        
        $get_directories ? ksort($arr) : sort($arr);
        return $arr;
    }
    
    /**
     * Converts file size to string that is human readible
     * @param integer $size The size in bytes
     * @return string
     */
    public static function sizeToString($size) 
    {

        if($size<1024) {
            return $size." bytes";
        }
        elseif($size<(1024*1024)) {
            $size=round($size/1024,1);
            return $size." KB";
        }
        elseif($size<(1024*1024*1024)) {
            $size=round($size/(1024*1024),1);
            return $size." MB";
        }
        else {
            $size=round($size/(1024*1024*1024),1);
            return $size." GB";
        }
    }

}

