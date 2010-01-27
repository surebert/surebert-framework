<?php

/**
 * Used to parse output text created with surebert textBling editor
 *
 * <code>
 * //returns the "cleaned" text as a string
 * echo sb_Text_Bling::parse"[b]here is a map[/b]"); 	//return the javascript for the bling string
 * echo sb_Text_Bling::get_javascript();
 * </code>
 *
 * @author Paul Visco  03/10/2008
 * @version 1.0 03/10/2008
 * @package sb_Text
 */

class sb_Text_BlingMedia extends sb_Text_Bling{
	/**
	 * The map format used, for embedded yahoo maps, set type to 'yahoo' and make sure to include yahoo map api script on your page <script type="text/javascript" src="http://maps.yahooapis.com/v3.04/fl/javascript/apiloader.js?appid=YOUR_API_KEY"></script>, You will need to repalce YOUR_API_KEY with your own yahoo flahs map API key
	 *
	 * @var string
	 */
	public static $map_type = 'yahoo'; 
	
	/**
	 * The default city for maps
	 *
	 * @var string
	 */
	public static $default_city='buffalo';
	
	/**
	 * The default state for maps
	 *
	 * @var string
	 */
	public static $default_state='ny'; 
	
	/**
	 * The maximum width for a flash file, otherwise resized propertionally
	 *
	 * @var integer
	 */
	public static $max_image_width = 400;
	
	/**
	 * The size of the video player's width and height
	 *
	 * @var array
	 */
	public static $flash_player_size = Array(
		'width' => 400,
		'height' => 335
	);
	
	/**
	 * Determines if external media is allowed inside image tags
	 *
	 * @var unknown_type
	 */
	public static $allow_external_images = 0;
	
	/**
	 * The path to the textBling video player used to play flv files
	 *
	 * @var string
	 */
	public static $custom_flv_player = '/media/sb_Text_Bling_Video.swf';
	
	/**
	 * The path to the textBling video player used to play mp3 files
	 *
	 * @var string
	 */
	public static $custom_mp3_player = '/media/sb_Text_Bling_Audio.swf';
	
	/**
	 * The path to the media e.g. [img]0107/test.gif[/img]  if content_path was ../content/users/paul then the image would be ../content/users/paul/0107/test.gif
	 *
	 * @var string
	 */
	public static $content_path = '';
	
	/**
	 * Converts [map][/map] to either a link to google maps or an embedded yahoo map
	 *
	 * @param string $str
	 * @return string
	 */
	public static function maps_to_html($str){
		
		switch(self::$map_type){
			
			//create inline yahoo map
			case 'yahoo':
				preg_match_all( "/\[map\](.*?)\[\/map\]/s", $str, $matches );
				$match = $matches[0];
				$data = $matches[1];
				$count = count($match);
				
				for($x=0;$x<$count;$x++){
					$city = self::$default_city;
					$addr = $data[$x];
					$state = self::$default_state;
					$zip ='';
					
					if (substr_count($data[$x], ",") != 0){
						$info = explode(",",$data[$x]);
						$addr = trim($info[0]);
						$city = trim($info[1]);
						if (isset($info[2]) && !empty($info[2])) { $state = strtoupper(trim($info[2]));}
						$zip = (isset($info[3]) && ctype_digit($info[3])) ? trim($info[3]) : '';
					}
					
					$uniqid =uniqid();
					$map_id = 'ymap_'.$uniqid;
					$marker_id = 'marker_'.$uniqid;
					$div_id = 'mapdiv_'.$uniqid;
					$widget_id = 'widget_'.$uniqid;
					
					$map = '<div id="'.$div_id.'" style="width:'.self::$flash_player_size['width'].'px;height:'.self::$flash_player_size['height'].'px;border:1px dotted #ACACAC;"></div> ';
					
					self::$javascript .= 'var '.$map_id .' = new Map("'.$div_id .'", "YahooDemo", "'.$addr.', '.$city.', '.$state.','.$zip.'", 3);'.$marker_id.' = new CustomPOIMarker( "Click", \'<a href="http://maps.yahoo.com/beta/index.php#maxp=search&amp;q1='.$addr.',+'.$city.',+'.$state.','.$zip.'&amp;mvt=m&amp;trf=0&amp;mag=3" >Visit</a> a full size map\', "'.$addr.', '.$city.', '.$state.','.$zip.'!", "0xFF8A00", "0xFFFFFF");'.$map_id.'.addMarkerByAddress( '.$marker_id.', "'.$addr.', '.$city.', '.$state.','.$zip.'");'.$widget_id.'= new SatelliteControlWidget();'.$map_id.'.addWidget('.$widget_id.');'.$map_id.'.addTool( new PanTool(), true );'.$map_id.'.addWidget(  new ZoomBarWidget());';
					
					$str = str_replace($match[$x], $map, $str);
					
				}
				
			//link to google map
			default:
				
				preg_match_all( "/\[map\](.*?)\[\/map\]/s", $str, $matches );
				$match = $matches[0];
				$data = $matches[1];
				$count = count($match);
				
				for($x=0;$x<$count;$x++){
					$city = self::$default_city;
					$addr = str_replace(" ", "%20", $data[$x]);
					$state = self::$default_state;
					
					if (substr_count($data[$x], ",") != 0)
					{
						$info = explode(",",$data[$x]);
						$addr = $info[0];
						$city = $info[1];
						if (!empty($info[2])) { $state = strtoupper($info[2]);}
					}
					
					$str = str_replace($match[$x], '<a class="blank" href="http://maps.google.com/maps?q='.$addr.'%2C'.$city.'%2C'.$state.'&t=h" title="click to search googlemap for '.$data[$x].'" >(MAP TO: '.strtoupper($addr).')</a>', $str);
				
				}
		}
		
		return $str;
	}
	
	
	/**
	 * Converts user uploaded, flash-based multimedia content to swf [flash][/flash], [flv][/flv] and [mp3][/mp3]
	 * @param string $str
	 * @return string
	 */
	public static function user_flash_to_swf($str){
		
		preg_match_all( "/\[flash\](.*?)\[\/flash\]/s", $str, $matches );
		$count = count($matches[1]);
		for($x=0;$x<$count;$x++)
		{
			$swf = self::$content_path.'/'.$matches[1][$x];
			$swf_info = getimagesize($swf);
			$width = $swf_info[0];
			$height = $swf_info[1];
			if ($width > self::$max_image_width){
				$width= 400;
				$height = round((400 * $height) / $width);
			}
			$uniqid = 'flash'.uniqid();
			
			if(self::$mobile ==1){
				$swf = '<object width="'.self::$flash_player_size['width'].'" height="'.self::$flash_player_size['height'].'"><param name="movie" value="'.$swf.'"><embed src="'.$swf.'"width="'.self::$flash_player_size['width'].'" height="'.self::$flash_player_size['height'].'"></embed></object>';
				
			} else {
				self::$javascript .= 'var uswf = new sb.swf({src:"'.$swf.'", width:"'.self::$flash_player_size['width'].'", height:"'.self::$flash_player_size['height'].'", bgColor:"#000000"});uswf.embed("#'.$uniqid.'");uswf=null;';
				$swf = '<p id="'.$uniqid.'"></p>';

			}
			
			$str=str_replace($matches[0][$x], $swf, $str);
		}
		
		preg_match_all( "/\[flv\](.*?)\[\/flv\]/s", $str, $matches );
		$count = count($matches[1]);
		for($x=0;$x<$count;$x++){
			$flv = self::$content_path.'/'.$matches[1][$x];
			if(!is_file($flv)){
				continue;
			}
			$flv_info = @getimagesize($flv);
			$width = $flv_info[0];
			$height = $flv_info[1];
			if ($width > self::$max_image_width){
				$width= self::$max_image_width;
				$height = round((self::$max_image_width * $height) / $width);
			}
			$uniqid = 'vid'.uniqid();
	
			$swf = $matches[1][$x];
			
			if(self::$mobile ==1){
				$swf = '<object width="'.self::$flash_player_size['width'].'" height="'.self::$flash_player_size['height'].'"><param name="movie" value="'.self::$custom_flv_player.'?video='.$flv.'" /><param name="wmode" value="transparent" /><embed src="'.self::$custom_flv_player.'?video='.$flv.'" type="application/x-shockwave-flash" wmode="transparent" width="'.self::$flash_player_size['width'].'" height="'.self::$flash_player_size['height'].'"></embed></object>';
				
			} else {
				self::$javascript .='var vid = new sb.swf({src:"'.self::$custom_flv_player.'?video='.$flv.'&debug=1", width: "'.self::$flash_player_size['width'].'", height:"'.self::$flash_player_size['height'].'", bgColor:"#ACACAC"});vid.embed("#'.$uniqid.'");vid=null;';
				
				$swf = '<p id="'.$uniqid.'"></p>';

			}
			
			$str=str_replace($matches[0][$x], $swf, $str);
		}
		
		
		preg_match_all( "/\[mp3\](.*?)\[\/mp3\]/s", $str, $matches );
		$count = count($matches[1]);
		for($x=0;$x<$count;$x++){
			$mp3 = self::$content_path.'/'.$matches[1][$x];
		
			$uniqid = 'mp3'.uniqid();
			
			if(self::$mobile ==1){
				$mp3 = '<object width="180" height="90"><param name="movie" value="'.self::$custom_mp3_player.'?file='.$mp3.'" /><param name="wmode" value="transparent" /><embed src="'.self::$custom_flv_player.'?file='.$mp3.'" type="application/x-shockwave-flash" wmode="transparent" width="180" height="90"></embed></object>';
				
			} else {
				self::$javascript .='var mp3 = new sb.swf({src:"'.self::$custom_mp3_player.'?file='.$mp3.'",width:"180", height:"90", bgColor:"#000000", version:6, alt: \' <a href="'.$mp3.'">::DOWNLOAD SOUND::</a> \'});mp3.embed("#'.$uniqid.'");mp3=null;';
				$mp3 = '<p id="'.$uniqid.'"></p>';

			}
			
			$str=str_replace($matches[0][$x], $mp3, $str);
		}
		
		return $str;
	}
	
	
	
	/**
	 * Convert external video links to embedded flash players [youtube][/youtube] and [gvideo][/gvideo]
	 *
	 * @param string $str
	 * @return string
	 */
	public static function external_video_to_player($str){
		
		### Youtube videos ###
		preg_match_all( "/\[youtube\](.*?)\[\/youtube\]/s", $str, $matches );
		$count = count($matches[1]);
		for($x=0;$x<$count;$x++){
			if(strstr($matches[1][$x], 'v=')){
				preg_match("~v=(.*)~", $matches[1][$x], $swf);
				$swf = $swf[1];
			} else {
				$swf = $matches[1][$x];
			}
			
			$uniqid = 'flash'.uniqid();
			
			if(self::$mobile ==1){
				$swf = '<object width="'.self::$flash_player_size['width'].'" height="'.self::$flash_player_size['height'].'"><param name="movie" value="http://www.youtube.com/v/'.$swf.'" /><param name="wmode" value="transparent" /><embed src="http://www.youtube.com/v/'.$swf.'" type="application/x-shockwave-flash" wmode="transparent" width="'.self::$flash_player_size['width'].'" height="'.self::$flash_player_size['height'].'"></embed></object>';
				
			} else {
				self::$javascript .='var uswf = new sb.swf({src:"http://www.youtube.com/v/'.$swf.'", width:"'.self::$flash_player_size['width'].'", height:"'.self::$flash_player_size['height'].'", bgColor:"#000000"});uswf.embed("#'.$uniqid.'");uswf=null;';
				$swf = '<p id="'.$uniqid.'"></p>';
			}
			
			$str=str_replace($matches[0][$x], $swf, $str);
			
		}
		
		### Google Video ###
		preg_match_all( "/\[gvideo\](.*?)\[\/gvideo\]/s", $str, $matches );
		$count = count($matches[1]);
		for($x=0;$x<$count;$x++){
			if(strstr($matches[1][$x], 'v=')){
				preg_match("~v=(.*)~", $matches[1][$x], $swf);
				$swf = $swf[1];
			} else {
				$swf = $matches[1][$x];
			}
			
			$uniqid = 'flash'.uniqid();
			
			if(self::$mobile ==1){
				$swf = '<object width="'.self::$flash_player_size['width'].'" height="'.self::$flash_player_size['height'].'"><param name="movie" value="http://video.google.com/googleplayer.swf?docId='.$swf.'" /><param name="wmode" value="transparent" /><embed src="http://video.google.com/googleplayer.swf?docId='.$swf.'" type="application/x-shockwave-flash" wmode="transparent" width="'.self::$flash_player_size['width'].'" height="'.self::$flash_player_size['height'].'"></embed></object>';
				
			} else {
				self::$javascript .='var uswf = new sb.swf({src:"http://video.google.com/googleplayer.swf?docId='.$swf.'", width:'.self::$flash_player_size['width'].', height:'.self::$flash_player_size['height'].', bgColor:"#000000"});uswf.embed("#'.$uniqid.'");uswf=null;';
				
				$swf = '<p id="'.$uniqid.'"></p>';

			}
			
			$str=str_replace($matches[0][$x], $swf, $str);
		}
		
		return $str;	
	}
	
		/**
	 * Converts non-flash multimedia files to quicktime e.g. wav, mid, amr,3gp, mp4
	 *
	 * @param string $str
	 * @return string
	 */
	public static function nonflash_media_to_html($str){
		
		preg_match_all( "~\[(wav|mid|amr|3gp|mp4)\](.*?)\[\/(wav|mid|amr|3gp|mp4)\]~s", $str, $matches );

		$count = count($matches[0]);
		
		for($x=0;$x<$count;$x++){
			
			$media = self::$content_path.'/'.$matches[2][$x];
			
			if(self::$mobile == 1) {
				
				$qt = '<a class="blank" href="'.$media.'" >::DOWNLOAD MEDIA::</a> ';
					
			} else {
				
				$w = ($matches[1][$x] == "3gp" || $matches[1][$x] == "mp4") ? "320" : "150";
			
				$h =  ($matches[1][$x] == "3gp" || $matches[1][$x] == "mp4") ? "256" : "16";
	
				$qt = '<div style="background-color:black;border:2px solid black;width:'.$w.'px;height:'.$h.'px"><object  classid="clsid:02BF25D5-8C17-4B23-BC80-D3488ABDDC6B" width="'.$w.'" height="'.$h.'" codebase="http://www.apple.com/qtactivex/qtplugin.cab" ><param name="src" value="'.self::$content_path.'/'.$matches[2][$x].'" /><param name="conroller" value="true" /><param name="autoplay" value="false" />';
				$qt .= '<object data="'.self::$content_path.'/'.$matches[2][$x].'" width="'.$w.'" height="'.$h.'" class="qt"><param name="controller" value="true" /><param name="autoplay" value="false" />No</object>';
				$qt .='</object></div>';
	
			}

			//replace media in the journal
			$str = str_replace($matches[0][$x], $qt, $str);
		}
		
		return $str;
	}
	
	/**
	 * Convert pdf tags to pdf links
	 *
	 * @param string $str
	 * @return string
	 */
	public static function pdf_to_link($str){
		
		preg_match_all( "~\[pdf\](.*?)\[/pdf\]~s", $str, $matches );
		$count = count($matches[1]);
		for($x=0;$x<$count;$x++){
			$pdf = '<a href="'.self::$content_path.'/'.$matches[1][$x].'">::READ PDF::</a>';
			
			$str=str_replace($matches[0][$x], $pdf, $str);
			
		}
		return $str;
	}
	
		/**
	 * Converts [img][/img] and [draw][/draw] to html images. draw tags are used when the user makes a drawing with the textBling clientside sketchpad
	 *
	 * @param string $str
	 * @return string
	 */
	public static function images_to_html($str){
		
		preg_match_all( "/\[(draw|img)=?(\w+)?\](.*?)\[\/(?:draw|img)\]/s", $str, $matches );
		
		$images = $matches[0];
		$num_images = count($images);
	
		for($x=0;$x<$num_images;$x++){
			$center = ($matches[2][$x] =='center') ? 1: 0;
			
			$className = ($matches[2][$x] =='left' || $matches[2][$x] =='right') ? 'tb_img_'.$matches[2][$x] : '';
			
			$alt = 'image';
			$caption = '';
			//get alt data if it exists in path
			$data = explode("|", $matches[3][$x]);
			
			if (count($data) > 1){
				$alt = $data[1];
			}
			
			if (count($data) > 2){
				$caption = $data[2];
			}
			
			$path = $data[0];
			
			
			if(self::$allow_external_images == 1 && preg_match("~^http~", $path)){
				$src = $path;
				$orig_src = $path;
				$thumb_src = $path;
			} else {
				$src = self::$content_path.'/'.$path;
				
				$orig_src = self::$content_path.'/'.dirname($path)."/".basename($path);
				
				$thumb_src = self::$content_path.'/'.dirname($path)."/th_".basename($path);
			
			}
			
			if(self::$mobile == 1){
				$src = $thumb_src;
			}
			
			$image ='';
			$path = ROOT.'/public/'.$src;
			if(is_file($path) || self::$allow_external_images == 1){
			
				$file_info = @getimagesize($path);		
			
				$type = $file_info['2']; //1 = GIF, 2 = JPG, 3 = PNG
				$width = $file_info[0];
				$height = $file_info[1];
				
				if(!empty($caption)){
					$image = '<div style="width:'.$width.'px;height'.$height.'px;"class="'.$className.'"><img  title="'.$alt.'" src="'.$src.'" '.$file_info[3].' alt="'.$alt.'" /><div class="tb_caption">'.$caption.'</div></div>';
				} else {
					$image = '<img class="'.$className.'" title="'.$alt.'" src="'.$src.'" '.$file_info[3].' alt="'.$alt.'" />';
				}
				
				if($center ==1){
					
					$image = '<div class="tb_center">'.$image.'</div>';
				}
			}
			
			if(!empty($image)){
				if(self::$mobile == 1){
					$image .= '<p><a href="'.$orig_src.'">::VIEW ORIGINAL IMAGE::</a></p>';
				}
			
				$str = str_replace($images[$x], $image, $str);
			}
		
		}
		
		return $str;
	}
	
	/**
	 * Clean up the text according to the textBling rules
	 *
	 * @param string $str The text to clean
	 * @return string The cleaned text
	 */
	public static function parse($str){
		
		$str = parent::clean($str);
		
		$str = self::pdf_to_link($str);
		$str = self::images_to_html($str);
		$str = self::nonflash_media_to_html($str);
		$str = self::user_flash_to_swf($str);
		$str = self::external_video_to_player($str);
		$str = self::maps_to_html($str);
		
		return $str;
		
	}
	
}

?>