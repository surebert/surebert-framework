<?php

/**
 * Used to parse output text created with surebert textBling editor
 *
 * <code>
 * //returns the "cleaned" text as a string
 * echo \sb\Text\Bling::parse"[b]here is a map[/b]");     //return the javascript for the bling string
 * echo \sb\Text\Bling::getJavascript();
 * </code>
 *
 * @author paul.visco@roswellpark.org
 * @package Text
 */

namespace sb\Text;

class BlingMedia extends Bling {
    
    /**
     * The default city for maps
     *
     * @var string
     */
    public static $default_city = 'buffalo';

    /**
     * The default state for maps
     *
     * @var string
     */
    public static $default_state = 'ny';

    /**
     * Determines if external media is allowed inside image tags
     *
     * @var unknown_type
     */
    public static $allow_external_images = 0;

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
    public static function mapsToHtml($str) {

        preg_match_all("/\[map\](.*?)\[\/map\]/s", $str, $matches);
        if(isset($matches[1])){
            $match = $matches[0];
            $data = $matches[1];
            $count = count($match);

            for ($x = 0; $x < $count; $x++) {
                $city = self::$default_city;
                $addr = str_replace(" ", "%20", $data[$x]);
                $state = self::$default_state;

                if (substr_count($data[$x], ",") != 0) {
                    $info = explode(",", $data[$x]);
                    $addr = $info[0];
                    $city = $info[1];
                    if (!empty($info[2])) {
                        $state = strtoupper($info[2]);
                    }
                }

                $str = str_replace($match[$x], '<a class="blank" href="http://maps.google.com/maps?q=' . $addr . '%2C' . $city . '%2C' . $state . '&t=h" title="click to search googlemap for ' . $data[$x] . '" >(MAP TO: ' . strtoupper($addr) . ')</a>', $str);
            }
        }
        
        return $str;
    }

    /**
     * Converts user uploaded, flash-based multimedia content and swfs to download link for archival purposes
     * 
     * Since swf is going end of life
     * @param string $str
     * @return string
     */
    public static function userFlashToSwf($str) {

        preg_match_all("/\[flash\](.*?)\[\/flash\]/s", $str, $matches);
        $count = count($matches[1]);

        for ($x = 0; $x < $count; $x++) {
            $swf = self::$content_path . '/' . $matches[1][$x];
            $path = ROOT . '/public/' . $swf;
            if (!(is_file($path) && filesize($path))) {
                continue;
            }
            
            $str = str_replace($matches[0][$x], '<p><a href="'.$swf.'">::Download Flash SWF::</a></p>', $str);
        }

        preg_match_all("/\[flv\](.*?)\[\/flv\]/s", $str, $matches);
        $count = count($matches[1]);
        for ($x = 0; $x < $count; $x++) {
            $flv = self::$content_path . '/' . $matches[1][$x];
            $path = ROOT . '/public/' . $flv;
            if (!(is_file($path) && filesize($path))) {
                continue;
            }
            
            $str = str_replace($matches[0][$x], '<p><a href="'.$flv.'">::Download Flash Video::</a></p>', $str);
        }


        return $str;
    }

    public static function mp3ToAudio($str) {
        $path = self::$content_path;
        return preg_replace_callback("~\[mp3\](.*?)\[\/mp3\]~s", function($match) use ($path) {
            $uniqid = 'mp3' . uniqid();
            $mp3 = $path . '/' . $match[1];
            
            $str = '<audio controls>
                <source src="'.$mp3.'" type="audio/mpeg">
                Your browser does not support inline playing of mp3 files.
              </audio>';
            $str .= '<p id="' . $uniqid . '"></p><p><a href="' . $mp3 . '">::DOWNLOAD SOUND::</a></p>';
            return $str;
        }, $str);
    }

    /**
     * Convert external video links to embedded flash players [youtube][/youtube] and [gvideo][/gvideo]
     *
     * @param string $str
     * @return string
     */
    public static function externalVideoToPlayer($str) {

        ### Youtube videos ###
        $str = preg_replace_callback("~\[youtube\](.*?)\[\/youtube\]~s", function($match) {
          
            if(empty($match[1])){
                return $match[0];
            }
            
            if (strstr($match[1], 'v=')) {
                preg_match("~v=(.*)~", $match[1], $movie);
                $movie = $movie[1];
            } else if (preg_match("~[\w-]{11}~", $match[1], $movie)) {
                $movie = $movie[0];
            } else {
                return $match[0];
            }
            
           return '<iframe id="ytplayer" type="text/html" width="100%" height="500px" src="https://www.youtube.com/embed/' . $movie . '?autoplay=1&origin=https://'.\sb\Gateway::$http_host.'" frameborder="0"></iframe>';
            
        }, $str);


        ### Vimeo videos ###
        $str = preg_replace_callback("~\[vimeo](.*?)\[\/vimeo]~s", function($match) {
            $movie = $match[1];
            $movie = preg_replace("~^http://vimeo.com/~", "", $movie);
            $str = '<iframe src="https://player.vimeo.com/video/' . $movie . '?title=0&amp;byline=0&amp;portrait=0" width="100%" height="500px" frameborder="0" webkitAllowFullScreen mozallowfullscreen allowFullScreen></iframe>';

            return $str;
        }, $str);

        ### Google Video ###
        $str = preg_replace("/\[gvideo\](.*?)\[\/gvideo\]/s", "(<strike>SORRY GOOGLE VIDEO IS NO LONGER AVAILABLE.  LINK WAS $1</strike>) ", $str);
//
        return $str;
    }

    /**
     * Converts non-flash multimedia files to quicktime e.g. wav, mid, amr,3gp, mp4
     *
     * @param string $str
     * @return string
     */
    public static function nonflashMediaToHtml($str) {

        preg_match_all("~\[(wav|mid|amr|3gp|mp4|avi|ogg)\](.*?)\[\/(wav|mid|amr|3gp|mp4|avi|ogg)\]~s", $str, $matches);

        if(isset($matches[2])){
            $count = count($matches[0]);

            for ($x = 0; $x < $count; $x++) {

                $media = self::$content_path . '/' . $matches[2][$x];
                $link = '<p><a class="blank" href="' . $media . '" >::DOWNLOAD MEDIA::</a><p>';

                //replace media in the journal
                $str = str_replace($matches[0][$x], $link, $str);
            }
        }
        

        return $str;
    }

    /**
     * Convert pdf tags to pdf links
     *
     * @param string $str
     * @return string
     */
    public static function pdfToLink($str) {

        preg_match_all("~\[pdf\](.*?)\[/pdf\]~s", $str, $matches);
        $count = count($matches[1]);
        for ($x = 0; $x < $count; $x++) {
            $pdf = '<a target="_blank" href="' . self::$content_path . '/' . $matches[1][$x] . '">::READ PDF::</a>';

            $str = str_replace($matches[0][$x], $pdf, $str);
        }
        return $str;
    }

    /**
     * Convert pdf tags to pdf links
     *
     * @param string $str
     * @return string
     */
    public static function textToLink($str) {

        preg_match_all("~\[txt](.*?)\[/txt]~s", $str, $matches);
        $count = count($matches[1]);
        for ($x = 0; $x < $count; $x++) {
            $str = str_replace($matches[0][$x], '<a target="_blank" href="' . self::$content_path . '/' . $matches[1][$x] . '">::READ TXT::</a>', $str);
        }
        return $str;
    }

    /**
     * Converts [img][/img] and [draw][/draw] to html images. draw tags are used when the user makes a drawing with the textBling clientside sketchpad
     *
     * @param string $str
     * @return string
     */
    public static function imagesToHtml($str) {

        preg_match_all("/\[(draw|img)=?(\w+)?\](.*?)\[\/(?:draw|img)\]/s", $str, $matches);

        $images = $matches[0];

        $num_images = count($images);

        for ($x = 0; $x < $num_images; $x++) {
            $center = ($matches[2][$x] == 'center') ? 1 : 0;

            $className = ($matches[2][$x] == 'left' || $matches[2][$x] == 'right') ? 'tb_img_' . $matches[2][$x] : 'tb_img';

            $alt = 'image';
            $caption = '';
            //get alt data if it exists in path
            $data = explode("|", $matches[3][$x]);

            if (count($data) > 1) {
                $alt = $data[1];
            }

            if (count($data) > 2) {
                $caption = $data[2];
            }

            $path = $data[0];

            if (self::$allow_external_images == 1 && preg_match("~^http~", $path)) {
                $src = $path;
                $orig_src = $path;
                $thumb_src = $path;
            } else {
                $src = self::$content_path . '/' . $path;

                $orig_src = self::$content_path . '/' . dirname($path) . "/" . basename($path);

                $thumb_src = self::$content_path . '/' . dirname($path) . "/thumbs/" . basename($path);
            }

            if (self::$mobile == 1) {
                $src = $thumb_src;
            }

            $image = '<p class="sb_image_missing">Missing Image ;(</p>';
            $path = ROOT . '/public/' . $src;
            if ((is_file($path) && filesize($path)) || self::$allow_external_images == 1) {

                $file_info = @getimagesize($path);
                $type = $file_info['2']; //1 = GIF, 2 = JPG, 3 = PNG
                $width = $file_info[0];
                $height = $file_info[1];

                if (!empty($caption)) {
                    $image = '<div style="width:' . $width . 'px;height' . $height . 'px;"class="' . $className . '"><img  title="' . $alt . '" src="' . $src . '" ' . $file_info[3] . ' alt="' . $alt . '" /><div class="tb_caption">' . $caption . '</div></div>';
                } else {
                    $image = '<img class="' . $className . '" title="' . $alt . '" src="' . $src . '" ' . $file_info[3] . ' alt="' . $alt . '" />';
                    if (self::$mobile == 1) {
                        $image = '<a href="' . $orig_src . '">' . $image . '</a>';
                    }
                }

                if ($center == 1) {

                    $image = '<div class="tb_center">' . $image . '</div>';
                }
            }

            if (!empty($image)) {

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
    public static function parse($str) {
        $str = parent::clean($str);
        $str = self::pdfToLink($str);
        $str = self::textToLink($str);
        $str = self::imagesToHtml($str);
        $str = self::nonflashMediaToHtml($str);

        $str = self::externalVideoToPlayer($str);
        $str = self::userFlashToSwf($str);
        $str = self::mp3ToAudio($str);
        $str = self::mapsToHtml($str);

        return $str;
    }

}
