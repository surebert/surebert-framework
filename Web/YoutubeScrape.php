<?php 

/**
 * used to work with youtube data
 * @author Paul Visco
 * @version 1.01 08/21/2007 12/08/2008
 * @package sb_Web
 */
class sb_Web_YoutubeScrape{
	
	/**
	 * Used to scrape flv content from youtube videos
	 *
	 * @author Paul Visco
	 * @param string $video_id The youtube video id.  This can be seen in the URL when viewing a youtube video e.g. http://youtube.com/watch?v=dLfIv049oi8 - "dLfIv049oi8" is the video id
	 * @return string flv binary content for the video referenced
	 * @example 
	 * <code>
	 * $flv = sb_Web_YoutubeScrape::fetch('dLfIv049oi8');
	 * file_put_contents('../_cache/myvid.flv', $flv); 
	 *
	 * </code>
	 */
	public static function fetch($video_id){
		$html = file_get_contents("http://www.youtube.com/watch?v=".$video_id);
		preg_match('~video_id=.*&t=.*&~', $html, $matches);
  		$flv_url = 'http://youtube.com/get_video?'.$matches[0];
  		
  		return file_get_contents($flv_url);
  		
	}
}

?>