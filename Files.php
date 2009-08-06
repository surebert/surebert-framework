<?php
/**
 * Various functions for working with files
 * @author Paul Visco
 * @package files
 * @version 1.25 11-19-07 06-16-09
 *
 */
class sb_Files{
	
	/**
	 * read a file into chunks for faster force download and better memory management
	 *
	 */
	public static function read_chunked($file_name,$return_bytes=true) { 
		
		 // how many bytes per chunk 
		$chunk_size = 1*(1024*1024);
		$buffer = ''; 
		$cnt =0; 
		
		$handle = fopen($file_name, 'rb'); 
		if ($handle === false) { 
			return false; 
		} 
		
		while (!feof($handle)) { 
			
			$buffer = fread($handle, $chunk_size); 
			echo $buffer; 
			ob_flush(); 
			flush(); 
			
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
	 * @return string e.g. audio/mpeg
	 * @return boolean returns false if not found
	 */
	public static function extension_to_mime($ext){
		
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
					$m = 'text/'.$ext;
					break;
				
				case 'flv':
					$m = 'video/x-flv';
					break;
					
				case 'mp3':
					$m = 'audio/mpeg';
					break;
					
				case 'mid':
					$m = 'audio/x-midi';
					break;

				case 'wav':
					$m = 'audio/x-wav';
					break;

                case 'zip':
                    $m = 'application/x-zip-compressed';
                    break;
                
                case 'doc':
                    $m = 'application/vnd.ms-word';
                    break;

                case 'xls':
                    $m = 'application/vnd.ms-excel';
                    break;

                case 'ppt':
                    $m = 'application/vnd.ms-powerpoint';
                    break;
                
                case 'docx':
                    $m = 'application/vnd.openxmlformats-officedocument.wordprocessingml.document';
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
    public static function file_to_mime($file){

        if(class_exists('finfo')){
            $finfo = @new finfo(FILEINFO_MIME, "/usr/share/misc/magic");

            if($finfo){
                /* get mime-type for a specific file */
                return $finfo->file($file);
            } else {
                $ext = strtolower(end(explode(".", basename($file))));
                return self::extension_to_mime($file);
            }
        }
    }
	
	/**
	 * Recursively deletes the files in a diretory
	 *
	 * @param string $dir The directory path
	 * @param boolean $del Should directory itself be deleted upon completion
	 * @return boolean
	 */
	public static function recursive_delete($dir, $del =0){
		
		$iterator = new RecursiveDirectoryIterator($dir);
		foreach (new RecursiveIteratorIterator($iterator, RecursiveIteratorIterator::CHILD_FIRST) as $file)
		{
		  if ($file->isDir()) {
		     rmdir($file->getPathname());
		  } else {
		     unlink($file->getPathname());
		  }
		}
		if($del ==1){
			rmdir($dir);
		}
	}
	
}

?>