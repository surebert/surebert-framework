<?php

/**
 * Used to parse output text created with surebert textBling editor
 * 
 * @author: Paul Visco  12/26/2004
 * @version: 2.71 11/04/2007
 * 
 * <code>
 * //returns the "cleaned" text as a string
echo sb_Text_Bling::clean"[b]here is a map[/b]\n[map]24 linwood avenue, buffalo, ny, 14209[/map]"); 	//return the javascript for the bling string
	echo sb_Text_Bling::get_javascript();
	</code>
*/
class sb_Text_Bling{
	
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
	 * The path to the textBling video player used to play flv files
	 *
	 * @var string
	 */
	public static $custom_flv_player = '/media/sb_TextBling_video.swf';
	
	/**
	 * The path to the textBling video player used to play mp3 files
	 *
	 * @var string
	 */
	public static $custom_mp3_player = '/media/sb_TextBling_audio.swf';
	
	/**
	 * The path to the media e.g. [img]0107/test.gif[/img]  if content_path was ../content/users/paul then the image would be ../content/users/paul/0107/test.gif
	 *
	 * @var string
	 */
	public static $content_path = '';
	
	/**
	 * If mobile is true than the media is linked instead of parsed and thumbnails are displayed instead of the images
	 *
	 * @var boolean
	 */
	public static $mobile = 0;
	
	/**
	 * The javascript required to script the cleaned text, e.g. add maps, media player, etc
	 *
	 * @var string
	 */
	protected static $javascript='';
	
	/**
	 * Returns the javascript produced by cleaning the text, this can lated be evaled or put in a script tag
	 *
	 * @return unknown
	 */
	public static function get_javascript($clear=1){
		$js = self::$javascript;
		if($clear ==1){
			self::$javascript ='';
		}
		return $js;
	}
	
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
	 * Converts emoticons shortcuts to images
	 *
	 * @param string $str
	 * @return string
	 */
	public static function emoticons_to_html($str){
		
		$str = str_replace (" :)", ' <img src="../media/emot/icon_biggrin.gif" alt="big_grin" />', $str);
		$str = str_replace (" :(", ' <img src="../media/emot/icon_cry.gif" alt="cry"  />', $str);
		$str = str_replace (" ;)", ' <img src="../media/emot/icon_wink.gif" alt="wink" />', $str);
		$str = str_replace (" 8*", ' <img src="../media/emot/icon_eek.gif" alt="eek" />', $str);
		
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
			if(is_file($swf)){
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
			
			
		}
		
		preg_match_all( "/\[flv\](.*?)\[\/flv\]/s", $str, $matches );
		
		$count = count($matches[1]);
		for($x=0;$x<$count;$x++){
			$flv = self::$content_path.'/'.$matches[1][$x];
			if(is_file($flv)){
				$flv_info = getimagesize($flv);
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
			
			
		}
		
		
		preg_match_all( "/\[mp3\](.*?)\[\/mp3\]/s", $str, $matches );
		$count = count($matches[1]);
		for($x=0;$x<$count;$x++){
			$mp3 = self::$content_path.'/'.$matches[1][$x];
		
			$uniqid = 'mp3'.uniqid();
			
			if(self::$mobile ==1){
				$mp3 = '<object width="180" height="90"><param name="movie" value="'.self::$custom_flv_player.'?file='.$mp3.'" /><param name="wmode" value="transparent" /><embed src="'.self::$custom_flv_player.'?file='.$mp3.'" type="application/x-shockwave-flash" wmode="transparent" width="180" height="90"></embed></object>';
				
			} else {
				self::$javascript .='var mp3 = new sb.swf({src:"'.self::$custom_flv_player.'?file='.$mp3.'",width:"180", height:"90", bgColor:"#000000", version:6, alt: \' <a href="'.$mp3.'">::DOWNLOAD SOUND::</a> \'});mp3.embed("#'.$uniqid.'");mp3=null;';
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
			$src = self::$content_path.'/'.$path;
			
			$orig_src = self::$content_path.'/'.dirname($path)."/".basename($path);
			
			$thumb_src = self::$content_path.'/'.dirname($path)."/th_".basename($path);
			
			if(self::$mobile == 1){
				$src = $thumb_src;
			}
			$image ='';
			
			if(is_file($src)){
			
				$file_info = @getimagesize($src);		
			
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
	 * Allow inline css
	 *
	 * @param string $str
	 * @return string
	 */
	public static function parse_css($str){
		
		//remove any javascript or expression calls from css
		$str = str_replace(Array("javascript", "expression"), "", $str);
		
		## font size can be any type of font size e.g.  1em, 12px, large
		return preg_replace( "~\[css=(.*?)\](.*?)\[\/css\]~s", '<span style="\\1;">\\2</span>', $str);
		
	}
	
	/**
	 * Clean up the text according to the textBling rules
	 *
	 * @param string $str The text to clean
	 * @param boolean $media Determines if media is parsed into html
	 * @return string The cleaned text
	 */
	public static function clean($str, $media=1){
		
		$str = self::typo_fix($str);
		
		$str = sb_Strings::html_escape_tags($str);
		
		$str = self::lists_to_html($str);
		
		$str = self::tables_to_html($str);
		
		$str = self::links_to_html($str);
	
		$str = self::colorize_instant_messages($str);
		
		$str = self::text_styles($str);
		
		$str = self::parse_css($str);
		
		$str = self::convert_quotes($str);
		
		$str = self::add_searches($str);
		
		$str = self::misc_tags($str);
		
		if($media ==1){
			$str = self::pdf_to_link($str);
			$str = self::images_to_html($str);
			$str = self::nonflash_media_to_html($str);
			$str = self::emoticons_to_html($str);
			$str = self::user_flash_to_swf($str);
			$str = self::external_video_to_player($str);
			$str = self::maps_to_html($str);
		}
		
		$str = nl2br($str);
		
		//turn any tabs into 4 spaces
		$str = str_replace("\t", "&nbsp;&nbsp;&nbsp;&nbsp;", $str);
		
		//remove any bling that is not processed
		//$str = self::strip_bling($str);
		
		return stripslashes($str);
	}
	
	/**
	 * Parses out misc tags such as horizontal rule and a scrolling box
	 *
	 * @param string $str
	 * @return string $str;
	 */
	public static function misc_tags($str){
		
		##hortizontal row
		$str =  str_replace('[hr]', '<hr style="clear:both;" />', $str);
		
		##line break
		$str =  str_replace('[br]', '<br />', $str);
		
		##puttext inside a scrolling box
		$str = preg_replace( "~\[box\](.*?)\[\/box\]~s", "<p class=\"box\">\\1</p>", $str);

		return $str;
	}
	
	
	/**
	 * Converts [list][/list] to ordered lists
	 *
	 * @param string $str
	 * @return string
	 */
	public static function lists_to_html($str){
		
		preg_match_all( "~\[list\](.*?)\[/list\]~s", $str, $matches );
		$match = $matches[0];
		$list = $matches[1];
		$count = count($match);
		
		for($x=0;$x<$count;$x++)		{
		
			//find each new line and make it a XHTML compliant <li>item</li>
			preg_match_all( "~^\w|\s.*$~m", $list[$x], $items );
			$num_items = count($items[0]);
			
			$item='';
			for($i=0;$i<$num_items;$i++)
			{
				//if it's not a blank row make a list item
				if (strlen(trim($items[0][$i])) != 0){
					$item  .= '<li>'.$items[0][$i].'</li>';
				}
			}
			
			$final_list = '<ul class="tb_ul">'.$item.'</ul>';
			$final_list = str_replace("\n", "", $final_list);
			$final_list = str_replace("\r", "", $final_list);
		
			//replace each List with the appropriate XHTML list
			$str = str_replace($match[$x], $final_list, $str);
			//reset list for next one
			$item = NULL;
		}
		
		preg_match_all( "~\[numlist\](.*?)\[/numlist\]~s", $str, $matches );
		$match = $matches[0];
		$list = $matches[1];
		$count = count($match);
		
		for($x=0;$x<$count;$x++)		{
		
			//find each new line and make it a XHTML compliant <li>item</li>
			preg_match_all( "~^\w|\s.*$~m", $list[$x], $items );
			$num_items = count($items[0]);
			
			$item='';
			for($i=0;$i<$num_items;$i++)
			{
				//if it's not a blank row make a list item
				if (strlen(trim($items[0][$i])) != 0){
					$item  .= '<li>'.$items[0][$i].'</li>';
				}
			}
			
			$final_list = '<ol class="tb_ol">'.$item.'</ol>';
			$final_list = str_replace("\n", "", $final_list);
			$final_list = str_replace("\r", "", $final_list);
		
			//replace each List with the appropriate XHTML list
			$str = str_replace($match[$x], $final_list, $str);
			//reset list for next one
			$item = NULL;
		}
		
		return $str;
	}
	
	public static function tables_to_html($str){
		
		//add the new ones
		preg_match_all("~\[table\](.*?)\[/table\]~s",$str, $tables);
		
		if(is_array($tables[1])){
			$x=0;
			foreach($tables[1] as $table){
				$rows = explode("\n", $table);
				$table = '<table class="tb_table">';
				$th =0;
				foreach($rows as $row){
					//if there is a pipe on the line
					if(strstr($row, "|")){
						$table .= "<tr>";
						
						$cells = explode("|", $row);
						
						foreach($cells as $cell){
							$td = ($th==0) ? 'th' : 'td';
							if(!empty($cell)){
								$table .="<".$td.">".$cell."</".$td.">";
							} else {
								$table .="<".$td.">".str_repeat("&nbsp;", 10)."</".$td.">";
							}
							
						}
						
						$table .= "</tr>";
						$th = 1;
					}
				
				}
				
				$table .= '</table>';
				
				$str = str_replace($tables[0][$x], $table, $str);
				$x++;
			}
				
		}
		
		return $str;
		
	}
	/**
	 * Converts email and http links to HTML links
	 *
	 * @param string $str
	 * @return string
	 */
	public static function links_to_html($str){
		
		### Convert Email Tags ###
		$str = preg_replace("#([\n ])([a-z0-9\-_.]+?)@([\w\-]+\.([\w\-\.]+\.)?[\w]+)#i", '<b> \\2 AT \\3 </b>', $str);
		
		### phrase links ###
		$str = preg_replace( "~\[link=(.*?)\](.*?)\[\/link\]~", "<a class=\"blank\" href=\"\\1\">\\2</a>", $str);
		
		### url links ###\\2://\\3
		$str = preg_replace("#(\s|\n)([a-z]+?)://([a-z0-9\-\.,\?!%\*_\#:;~\\&$@\/=\+]+)#i", ' <a href="\\2://\\3" title="\\2://\\3">(VISIT LINK)</a>', $str); 
		
		return $str;
	}
	
	
	/**
	 * Adds search tags to link text to searches both on and off site
	 *
	 * @param string $str
	 * @return string
	 */
	public static function add_searches($str){
		
		### make google searches ###
		preg_match_all( "/\[google\](.*?)\[\/google\]/s", $str, $matches );
		$match = $matches[0];
		$data = $matches[1];
		$count = count($match);
		
		for($x=0;$x<$count;$x++){
			$query = str_replace(" ", "+", $data[$x]);
			$query = str_replace('"', "%22", $query);
			$str = str_replace($match[$x], '<a class="blank" href="http://www.google.com/search?hl=en&amp;ie=UTF-8&amp;q='.$query.'" title="click to search google for '.$data[$x].'" >(GOOGLE - '.$data[$x].')</a>', $str);
		
		}

		### make wikipedia searches ###
		$str = preg_replace( "~\[wikipedia\](.*?)\[\/wikipedia\]~s", '<a class="blank"  href="http://en.wikipedia.org/wiki/Special:Search?search='.str_replace(" ", "_", "\\1").'&amp;go=Go"  title="click to search wikipedia for \\1" >(WIKIPEDIA - \\1)</a>', $str );
		
		### make wiktionary searches ###
		$str = preg_replace( "~\[dict\](.*?)\[\/dict\]~s", '<a class="blank"  href="http://en.wiktionary.org/wiki/Special:Search?search='.str_replace(" ", "_", "\\1").'&amp;go=Go"  title="click to search wiktionary for \\1">(WIKTIONARY - \\1)</a>', $str );
		
		return $str;
	}
	
	/**
	 * Colorizes instant message conversation within [im][/im] tags
	 *
	 * @param string $str
	 * @return string
	 */
	public static function colorize_instant_messages($str){
		
		preg_match_all( "/\[im\](.*?)\[\/im\]/s", $str, $matches );
		
		$count = count($matches[1]);
		$str = preg_replace("~\[(im|\/im)\]~", "", $str);

		for($x=0;$x<$count;$x++){
			preg_match_all( "~(.*?: )~", $matches[1][$x], $ims );
			
			$names = array_unique($ims[1]);
			$num_names = count($names);
		
			$im=0;
			foreach($names as $name){
				if ($im%2) {$color="red"; } else {$color="blue";}

				$str = str_replace($name, '<b><i><span style="color:'.$color.'">'.$name.'</span></i></b>', $str);
				$im++;
			}
			
		}
		
		return $str;
	}
	
	/**
	 * Converts quotes into quoted text blocks
	 *
	 * @param unknown_type $str
	 */
	public static function convert_quotes($str){
		
		preg_match_all( "/\[q\](.*?)\[\/q\]/s", $str, $matches );
		$match = $matches[0];
		$data = $matches[1];
		$count = count($match);

		for($x=0;$x<$count;$x++){
			

			$str = str_replace($match[$x], '<blockquote class="quote"><p>'.$data[$x].'</p></blockquote>', $str);
		}
		
		return $str;
	}
	
	/**
	 * Strip textBling tags out of a string
	 *
	 * @param string $str
	 * @return string
	 */
	public static function strip_bling($str){
		return preg_replace('~\[.*?](.*?)\[.*?]~', "$1", $str);
	}
	
	/**
	 * Strips everything including textBlng tags, this is useful for RSS feed and text summaries in search results
	 *
	 * @param string $str
	 * @return string
	 */
	public static function strip_all($str){
		$str = stripslashes($str);
		$str = strip_tags($str);
		$str = sb_Strings::unicode_urldecode($str);
		$str = self::strip_bling($str);
		$str = sb_Strings::strip_microsoft_chars($str);
		return $str;
	}
	
	/**
	 * Fixed common typos, can be used directly as it is a static property
	 *
	 * @param the text to be cleaned $str
	 * @return string
	 * 
	 * @example textBling::typo_fix('Teh bird cant fly'); returns 'The bird can't fly'
	 */
	public static function typo_fix($str){
		
		//mistakes
		$common_typos = array(
			
			"adn"=>"and",
			"agian"=>"again",
			"ahve"=>"have",
			"ahd"=>"had",
			"alot"=>"a lot",
			"amke"=>"make",
			"arent"=>"aren't",
			"beleif"=>"belief",
			"beleive"=>"believe",
			"broswer"=>"browser",
			"cant"=>"can't",
			"cheif"=>"chief",
			"couldnt"=>"couldn't",
			"comming"=>"coming",
			"didnt"=>"didn't",
			"doesnt"=>"doesn't",
			"dont"=>"don't",
			"ehr"=>"her",
			"esle"=>"else",
			"eyt"=>"yet",
			"feild"=>"field",
			"goign"=>"going",
			"hadnt"=>"hadn't",
			"hasnt"=>"hasn't",
			"hda"=>"had",
			"hed"=>"he'd", 
			"hel"=>"he'll", 
			"heres"=>"here's", 
			"hes"=>"he's", 
			'hers'=>"her's",
			"hows"=>"how's", 
			"hsa"=>"has", 
			"hte"=>"the", 
			"htere"=>"there",
			"i'll"=>"I'll",
			"infromation"=>"information",
			"i'm"=>"I'm",
			"isnt"=>"isn't", 
			"itll"=>"it'll", 
			"itsa"=>"its a",
			"ive"=>"I've",
			"mkae"=>"make",
			"peice"=>"piece",
			"seh"=>"she",
			"shouldnt"=>"shouldn't", 
			"shouldve"=>"should've",
			"shoudl"=>"should",
			 "somethign"=>"something",
			"taht"=>"that", 
			"tahn"=>"than", 
			"Teh"=>"The",
			"teh"=>"the",
			"taht"=>"that",
			"thier"=>"their",
			"weve"=>"we've",
			"workign"=>"working"
	
		);
		
		foreach($common_typos as $typo=>$correction){
			$str = preg_replace("~\b".$typo."\b~", $correction, $str);
		}
		
		//fix ; fragments
		$str = str_replace("n;t", "n't", $str);
		
		return $str;
		

	}
	
	/**
	 * Converts text style tags
	 *
	 * @param string $str
	 * @return string $str;
	 */
	public static function text_styles($str){
				
		##bold
		$str=str_replace('[b]', '<strong class="tb_b">', $str);
		$str=str_replace('[/b]', '</strong>', $str);
		$str=str_replace(array("[/s]", "[/strike]"), "</span>", $str);
		
		##italic
		$str=str_replace(Array('[i]', '[em]'), '<em class="tb_i">', $str);
		$str=str_replace(Array('[/i]', '[/em]'), '</em>', $str);
		
		##underline
		$str=str_replace('[u]', '<u class="tb_u">', $str);
		$str=str_replace('[/u]', '</u>', $str);
		
		##quote
		$str=str_replace(Array('[q]','[quote]'), '<q class="tb_q">', $str);
		$str=str_replace(Array('[/q]','[/quote]'), '</q>', $str);
		
		##cite
		$str=str_replace('[cite]', '<cite class="tb_cite">', $str);
		$str=str_replace('[/cite]', '</cite>', $str);
		
		##hilite
		$str=str_replace(Array('[h]', '[hilite]'), '<span class="tb_hilite">', $str);
		$str=str_replace(Array('[/h]', '[/hilite]'), '</span>', $str);
		
		##line-though
		$str=str_replace('[strike]', '<span class="tb_strike">', $str);
		$str=str_replace('[/strike]', "</span>", $str);
		
		## add small caps
		$str=str_replace("[caps]", '<span class="tb_caps">', $str);
		$str=str_replace("[/caps]", "</span>", $str);

		## font size
		$str = preg_replace( "~\[size=(.*?)\](.*?)\[\/size\]~s", '<span style="font-size:\\1;">\\2</span>', $str );
		
		## font color
		preg_match_all( "/(\[color=(\w+|\W\w+)\])(.*?)\[\/color\]/s", $str, $matches );
		$match = $matches[0];
		$color = $matches[2];
		$content = $matches[3];
		$count = count($match);
		
		for($x=0;$x<$count;$x++){
		
			if (strlen($color[$x]) == 1){
				$str = str_replace($match[$x], '<span class="'.$color[$x].'">'.$content[$x].'</span>', $str);
				
			} else {
				
				$str = str_replace($match[$x], '<span style="color:'.$color[$x].'">'.$content[$x].'</span>', $str);
			}
			
		}
		
		return $str;
	}
}
?>