<?php

/**
 * Used to parse output text created with surebert textBling editor
 *
 * <code>
 * //returns the "cleaned" text as a string
 * echo \sb\Text\Bling::clean"[b]here is a map[/b]\n[map]24 linwood avenue, buffalo, ny, 14209[/map]");     //return the javascript for the bling string
 * 
 * echo \sb\Text\Bling::getJavascript();
 * </code>
 *
 * @author paul.visco@roswellpark.org
 * @package Text
 * 
*/
namespace sb\Text;
class Bling{
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
    public static function getJavascript($clear=1)
    {
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
    public static function emoticonsToHtml($str)
    {
        
        $str = str_replace (" :)", ' <img src="/media/emot/icon_biggrin.gif" alt="big_grin" />', $str);
        $str = str_replace (" :(", ' <img src="/media/emot/icon_cry.gif" alt="cry"  />', $str);
        $str = str_replace (" ;)", ' <img src="/media/emot/icon_wink.gif" alt="wink" />', $str);
        $str = str_replace (" 8*", ' <img src="/media/emot/icon_eek.gif" alt="eek" />', $str);
        
        return $str;
    }
    
    /**
     * Allow inline css
     *
     * @param string $str
     * @return string
     */
    public static function parseCss($str)
    {

        return  preg_replace_callback("~\[css=(.*?)\](.*?)\[\/css\]~s", function($match){
            return '<span style="'.str_replace(Array('javascript', 'expression'), '', $match[1]).'">'.$match[2].'</span>';
        }, $str);
        
    }
    
    /**
     * Clean up the text according to the textBling rules
     *
     * @param string $str The text to clean
     * @param boolean $media Determines if media is parsed into html
     * @return string The cleaned text
     */
    public static function clean($str, $allow_email=false)
    {
        
        $str = self::typoFix($str);
        
        $str = HTML::escape($str);
        
        $str = self::convertQuotes($str);

        $str = self::listsToHtml($str);
        
        $str = self::tablesToHtml($str);
        
        $str = self::linksToHtml($str, $allow_email);
    
        $str = self::colorizeInstantMessages($str);
        
        $str = self::textStyles($str);
        
        $str = self::parseCss($str);
        
        $str = self::addSearches($str);
        
        $str = self::miscTags($str);
        
        //turn any tabs into 4 spaces
        $str = str_replace("\t", "&nbsp;&nbsp;&nbsp;&nbsp;", $str);
        
        return $str;
    }
    
    /**
     * Parses out misc tags such as horizontal rule and a scrolling box
     *
     * @param string $str
     * @return string $str;
     */
    public static function miscTags($str)
    {
        
        ##hortizontal row
        $str =  str_replace('[hr]', '<hr style="clear:both;" />', $str);
        
        ##line break
        $str =  str_replace('[br]', '<br />', $str);
        
        ##puttext inside a scrolling box
        $str = preg_replace( "~\[box\]\n?(.*?)\n?\[\/box\]\n{1,}?~is", "<div class=\"box\">\\1</div>", $str);

        return $str;
    }
    
    /**
     * Converts [list][/list] to ordered lists
     *
     * @param string $str
     * @return string
     * @todo combine numlist and list into one
     */
    public static function listsToHtml($str)
    {

        $str = preg_replace_callback('/(?:(?:^|\n)[#\*].*)+\n?/m', function($match){
            
            $type = substr(trim($match[0]), 0, 1) == '#' ? 'ol' : 'ul';
            
            $star_cnt = 1;
            $lis = preg_replace_callback("~^([\*\#]+)(.*)$~m", function($innermatch){
                
                return '<li>'.$innermatch[2].'</li>';
            }, trim($match[0]));

            $lis = str_replace(Array("\n</li>", "</li>\n"), "</li>", $lis);
            return '<'.$type.' class="tb">'.$lis.'</'.$type.'>';

        }, $str);

        $str = preg_replace_callback("~\[list\](.*?)\[/list\]\n~s", function($match){

            $lis = preg_replace_callback("~^\w|\s.*$~m", function($inner_match){
                $li = trim($inner_match[0]);
                if(!empty($li)){
                    return '<li class="tb">'.trim($li).'</li>';
                }
                return '';
            }, $match[1]);
            return '<ul class="tb">'.$lis.'</ul>';

        }, $str);

        $str = preg_replace_callback("~\[numlist\](.*?)\[/numlist\]\n?~s", function($match){

            $lis = preg_replace_callback("~^\w|\s.*$~m", function($inner_match){
                $li = trim($inner_match[0]);
                if(!empty($li)){
                    return '<li class="tb">'.trim($li).'</li>';
                }
                return '';
            }, $match[1]);
            return '<ol class="tb">'.$lis.'</ol>';

        }, $str);
        
        $str = preg_replace_callback('~\n{0,}\t{0,}\[(/?(ol|ul|li))\]\n*~', function($match) {
            if (!strstr($match[1], "/")) {
                return '<' . $match[1] . ' class="tb">';
            } else {
                return '<' . $match[1] . '>';
            }
        }, $str);

        return $str;
    }
    
    public static function tablesToHtml($str)
    {
        
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
    public static function linksToHtml($str, $allow_email=false, $link_markup=null)
    {
        
        ### Convert Email Tags ###
        if(!$allow_email){
            $str = preg_replace("#([\n ])([a-z0-9\-_.]+?)@([\w\-]+\.([\w\-\.]+\.)?[\w]+)#i", '<b> \\2 AT \\3 </b>', $str);
        } else {
            $str = preg_replace("#([\n ])([a-z0-9\-_.]+?)@([\w\-]+\.([\w\-\.]+\.)?[\w]+)#i", ' <a href="mailto:\\2@\\3">\\2@\\3</a>', $str);
        }

        ### phrase links ###
        $str = preg_replace( "~\[(?:url|link)=(.*?)\](.*?)\[\/(?:url|link)\]~", "<a class=\"blank\" href=\"\\1\">\\2</a>", $str);

        $link = $link_markup ? $link_markup : '(LINK)';
        ### url links ###\\2://\\3
        //$str = preg_replace("#(\s|\n)([a-z]+?)://([a-z0-9\-\.,\?!%\*_\#:;~\\&$@\/=\+]+)#i", ' <a href="\\2://\\3" title="\\2://\\3">'.$link.'</a>', $str);

        $str = preg_replace_callback("#(^|\s)([a-z]+?://[\w\-\.,\?!%\*\#:;~\\&$@\/=\+]+)#i", function($match) use($link){
            $href = $match[2];
            $end_punct = '';

            if(preg_match("~[\.\?\!]$~", $href, $matchx)){
                $end_punct = $matchx[0];
                $href = substr($href,0,-1);
            }
            return $match[1].'<a href="'.$href.'" title="'.$href.'">'.$link.'</a>'.$end_punct;
        }, $str);

        return $str;
    }
    
    
    /**
     * Adds search tags to link text to searches both on and off site
     *
     * @param string $str
     * @return string
     */
    public static function addSearches($str)
    {
        
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
    public static function colorizeInstantMessages($str)
    {
        
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
    public static function convertQuotes($str)
    {

        $r = "/\[q(?:uote)?\](.*?)\[\/q(?:uote)?\]/is";
        while(preg_match($r, $str)){
            $str = preg_replace($r, '<blockquote class="quote"><p>\\1</p></blockquote>', $str);
        }

        return $str;
    }
    
    /**
     * Strip textBling tags out of a string
     *
     * @param string $str
     * @return string
     */
    public static function stripBling($str)
    {
        return preg_replace('~\[.*?](.*?)\[.*?]~', "$1", $str);
    }
    
    /**
     * Strips everything including textBlng tags, this is useful for RSS feed and text summaries in search results
     *
     * @param string $str
     * @return string
     */
    public static function stripAll($str)
    {
        $str = stripslashes($str);
        $str = strip_tags($str);
        $str = Strings::unicodeUrldecode($str);
        $str = self::stripBling($str);
        $str = Strings::stripMicrosoftChars($str);
        return $str;
    }
    
    /**
     * Fixed common typos, can be used directly as it is a static property
     *
     * <code>
     * textBling::typoFix('Teh bird cant fly');
     * //returns 'The bird can't fly'
     * </code>
     *
     * @param the text to be cleaned $str
     * @return string
     * 
     */
    public static function typoFix($str)
    {
        
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
    public static function textStyles($str)
    {
                
        ##bold
        $str = preg_replace("~\[b\](.*?)\[/b\]~is", '<strong class="tb_b">$1</strong>', $str);

        ##sup
        $str = preg_replace("~\[sup\](.*?)\[/sup\]~is", '<sup class="tb_sup">$1</sup>', $str);

        ##sub
        $str = preg_replace("~\[sub\](.*?)\[/sub\]~is", '<sub class="tb_sub">$1</sub>', $str);

        ##italic
        $str = preg_replace("~\[i\](.*?)\[/i\]~is", '<em class="tb_i">$1</em>', $str);
        
        ##underline
        $str = preg_replace("~\[u\](.*?)\[/u\]~is", '<u class="tb_u">$1</u>', $str);
        
        ##cite
        $str = preg_replace("~\[cite\](.*?)\[/cite\]~is", '<cite class="tb_cite">$1</cite>', $str);
        
        ##hilite
        $str = preg_replace("~\[h(?:ilite)?\](.*?)\[/h(?:ilite)?\]~is", '<span class="tb_hilite">$1</span>', $str);
        
        ##line-though
        $str = preg_replace("~\[strike](.*?)\[/strike]~is", '<del class="tb_strike">$1</del>', $str);
        
        ## add small caps
        $str = preg_replace("~\[caps](.*?)\[/caps]~is", '<span class="tb_caps">$1</span>', $str);

        ## add center
        $str = preg_replace("~\[center](.*?)\[/center]~is", '<center class="tb_center">$1</center>', $str);
        
        ## add code tag
        ## add small caps
        $str = preg_replace("~\[code](.*?)\[/code]~is", '<pre style="background-color:black;color:green;overflow:auto;">$1</pre>', $str);

        ## font size
        $r = "~\[size=([\d(?:\.\d+)]+(?:em|px)?)\](.*?)\[\/size\]~is";
        while(preg_match($r, $str)){
            $str = preg_replace($r, '<span style="font-size:\\1;">\\2</span>', $str);
        }

        ## font size
        $r = "~\[size=(small|medium|large)\](.*?)\[\/size\]~is";
        while(preg_match($r, $str)){
            $str = preg_replace($r, '<span style="font-size:\\1;">\\2</span>', $str);
        }

        ## font color
        $r = "~\[color=(.*?)\](.*?)\[\/color\]~is";
        while(preg_match($r, $str)){
            $str = preg_replace($r, '<span style="color:\\1;">\\2</span>', $str);
        }
        
        return $str;
    }
}
