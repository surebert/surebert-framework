<?php

/**
 * Used to parse output text created with surebert textBling editor
 * 
 * @author Paul Visco  12/26/2004
 * @version 2.71 11/04/2007
 * @package sb_Text
 * <code>
 * //returns the "cleaned" text as a string
echo sb_Text_Bling::clean"[b]here is a map[/b]\n[map]24 linwood avenue, buffalo, ny, 14209[/map]"); 	//return the javascript for the bling string
	echo sb_Text_Bling::get_javascript();
	</code>
*/
class sb_Text_Bling{
	/*
	 * If mobile is true than the media is linked instead of parsed and thumbnails are displayed instead of the images
	 *
	 * @var boolean
	 */
	public static $mobile = 0;
	
	/**
	 * The javascript required to script the cleaned text
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
		
		//$str = nl2br($str);
		
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
		$str = preg_replace("#(\s|\n)([a-z]+?)://([a-z0-9\-\.,\?!%\*_\#:;~\\&$@\/=\+]+)#i", ' <a href="\\2://\\3" title="\\2://\\3">(LINK)</a>', $str); 
		
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
		$str = preg_replace( "~\[wiktionary\](.*?)\[\/wiktionary\]~s", '<a class="blank"  href="http://en.wiktionary.org/wiki/Special:Search?search='.str_replace(" ", "_", "\\1").'&amp;go=Go"  title="click to search wiktionary for \\1">(WIKTIONARY - \\1)</a>', $str );
		
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