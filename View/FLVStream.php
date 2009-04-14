<?php

/**
 * 
 * @author Paul Visco 06/05/2007
 * @version 1.0 07/07/2007 12/08/2008
 * @example 
 * 
 * <code>
 * sb_Streaming::flv('../content/love.flv', 20154);
 * </code>
 */

class sb_View_FLVStream extends sb_View{
	
	/**
	 * The base directory in which the videos are found
	 *
	 * @var string
	 */
	protected $base_dir = '/private/resources/';
	
	/**
	 * Used to stream flv files to a flash video player, can be used to allow forwarding to parts of the video not yet loaded.  Adapted from code at www.flashguru.com
	 * 
	 *
	 * @param string $video The path to the video
	 * @param integer $position  The position in the video to play from
	 */
	public function template_not_found($video){
		
		$seekat = $this->args[0];
		$filename = basename($video);
		
		$ext=strrchr($filename, ".");
		$file = ROOT.$this->base_dir.'/'.$filename;
		
		if((file_exists($file)) && ($ext==".flv")){
			
		        header("Content-Type: video/x-flv");
		        
		        if($seekat != 0) {
	                echo("FLV");
	                echo(pack('C', 1 ));
	                echo(pack('C', 1 ));
	                echo(pack('N', 9 ));
	                echo(pack('N', 9 ));
		        }
		        
		        $fh = fopen($file, "rb");
		        fseek($fh, $seekat);
		        while (!feof($fh)) {
		          echo (fread($fh, filesize($file))); 
		        }
		        
		        fclose($fh);
		        
		} else{
			echo("ERORR: The file does not exist");
		}

	}
	
}

?>