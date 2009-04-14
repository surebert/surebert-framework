<?php

/**
 * Uploads media files to content folder.  Used in ../data/index.php for accepting uploads from surebert's clienside flash upload and transferring the data to the ../contents folder
 * 
 * @author Paul Visco 06/01/2007
 * @version 2.0 07/07/2007
 * @example 
 * 
 * <code>
 * //checks for file data from surebert flash/javascript uploader
if(isset($_FILES['Filedata'])){
	
	//debug flash file uploads
	//file_put_contents("../_cache/files.txt", "\n\n".date("m/d/y H:i:s").print_r($_FILES, 1), FILE_APPEND);
	
	//set max upload sizes
	set_time_limit('600');
	ini_set("max_input_time", "600");
	
	session_id($_GET['s']);
}

//include the configuration files, defintions, mssql login
require_once('../_config/config.php');

//upload files if they exist
if(isset($_FILES['Filedata'])){
	
	//turn it into blob data for sql insert
	//$blob = file_get_contents($_FILES['Filedata']['tmp_name']);
	
	$uploaded = sb_Upload::file($_FILES['Filedata'], 'demo_uploads');
	echo '../content/demo_uploads/'.$uploaded->name;
		if($uploaded){
			if(!isset($_SESSION['uploaded'])){
				$_SESSION['uploaded'] = Array();
			}
			
			array_push($_SESSION['uploaded'], $uploaded);
			$response = $uploaded;
		}
		
}</code>

 */
class sb_Upload {
	
	public static $keep_original = 1;
	
	public static function file($file, $subdirectory){
		
		$dir = ROOT.'/public/content';
		
		if(!is_dir($dir)){
			if(!mkdir($dir, 0777)){
				return 0;
			}
		}
		
		$dir .= $subdirectory;
		
		if(!is_dir($dir)){
			if(!mkdir($dir, 0777)){
				return 0;
			}
		}
		
		$dir .= '/'.date('my');
		
		if(!is_dir($dir)){
			if(!mkdir($dir, 0777)){
				return 0;
			}
		}
		
		$thumbs = $dir.'/thumbs';
		
		if(!is_dir($thumbs)){
			if(!mkdir($thumbs, 0777)){
				return 0;
			}
		}
		
		
	
		$clean = sb_Strings::clean_file_name($file['name']);
		$new_file = $dir.'/'.$clean;
		$width = null;
		$height = null;
		
		if(move_uploaded_file($file['tmp_name'], $new_file)){
			switch(strtolower(end(explode('.',$clean)))){
				case 'png':
				case 'gif':
				case 'jpg':
					$tag = 'img';
					$info = getimagesize($new_file);
					$width = $info[0];
					$height = $info[1];
					
					$sb_image = new sb_Image();
					$thumb = $dir.'/thumbs/'.$clean;
					$display = $dir.'/'.$clean;
					$original = $dir.'/orig/'.$clean;
					
					//create the thumb image and resize it
					$sb_image->set($new_file, $thumb);
					
					$sb_image->resize(120, -1);
					$sb_image->to_file();
					
					//create the original size image
					if(self::$keep_original ==1){
						$orig = $dir.'/orig';
						if(!is_dir($orig)){
							if(!mkdir($orig, 0777)){
								return 0;
							}
						}
						copy($new_file, $original);
					}
					
					//create the display image
					if($info[0] > 530){
						$sb_image->set($new_file, $display);
						
						$sb_image->resize(530, -1);
						$sb_image->to_file();
						
					} else {
						copy($new_file, $display);
					}
					
					$info = getimagesize($display);
					$width = $info[0];
					$height = $info[1];
					$size = round((filesize($display)/1000)).'k';
					
					break;
					
				case 'mp3':
					$tag = 'mp3';
					break;
					
				case 'swf':
					$tag = 'flash';
					break;
					
				case 'flv':
					//requires flvtool2 and ffmpeg to add position data and make thumbnail
				
					$clean='flv_'.$clean;
					$command = "flvtool2 -U ".$new_file." ".$dir.'/'.$clean;
					
					exec($command);
					$jpeg_frame = str_replace(".flv", "_%d.jpg", $dir.'/'.$clean);
					
					$command = "ffmpeg -i ".$dir.'/'.$clean." -an -ss 00:00:03 -t 00:00:01 -r 1 -y -s 400x300 ".$jpeg_frame;
					exec($command);
					
					$sb_image = new sb_Image();
					
					$jpeg_frame = str_replace("_%d.jpg", "_1.jpg", $jpeg_frame);
					$thumb = str_replace("flv_", "thumbs/flv_", $jpeg_frame);
					
					//create the thumb image and resize it
					$sb_image->set($jpeg_frame, $thumb);
					
					$sb_image->resize(120, -1);
					$sb_image->to_file();
					
					
					$info = getimagesize($new_file);
					$size = number_format(round((filesize($new_file)/1000))).'k';
					
					unlink($new_file);
					
					$tag = 'flv';
					break;
					
				case 'pdf':
					$tag = 'pdf';
					break;
			}
			$file = new stdClass();
			$file->tag = $tag;
			$file->width = $width;
			$file->height = $height;
			$file->size = $size;
			$file->name = date('my').'/'.$clean;
			$file->code = '['.$file->tag.']'.$file->name.'[/'.$file->tag.']';'
			return $file;
			
		} else {
			return 0;
		}
		
	}
}
?>