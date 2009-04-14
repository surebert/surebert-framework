<?php
/**
 * Describes an email attachment, when found they are attached to sb_Email objects
 * 
 * @author: Paul Visco
 * @package: email
 * @version 2.01 07/09/2007
 *
 */
class sb_Email_Attachment{
	
	/**
	 * The extension of the attachment file
	 *
	 * @var string
	 */
	public $extension='';
	
	/**
	 * The file name of the file attachment e.g. gif, jpg, png, wav, mp3, etc
	 *
	 * @var string
	 */
	public $name='';
	
	/**
	 * The path to the attachment on the local drive after reading or before writing
	 *
	 * @var string
	 */
	public $filepath='';
	
	/**
	 * The encoding type of the attchment
	 * 
	 * 0 = 7bit, 1= 8bit, 2=binary, 3=base64, 4=quoted prinatble, 5=other			
	 *
	 * @var integer
	 */
	public $encoding;
	
	/**
	 * The subtype of the attachment
	 * 
	 * JPEG, WAV, HTML, PLAIN, etc			
	 *
	 * @var string
	 */
	public $subtype;
	
	/**
	 * The type of the attachment
	 * 
	 * type 0 = text, 1 = multipart, 2 = message, 3 = application, 4 = audio, 5= image, 6= video, 7 = other		
	 *
	 * @var interger
	 */
	public $type;
	
	/**
	 * The file size in K
	 *
	 * @var float
	 */
	public $sizeK;
	
	/**
	 * The binary content of the attachment
	 *
	 * @var string
	 */
	public $contents;
	
	/**
	 * The mime type of the attachment, used when sending
	 *
	 * @var string e.g. image/jpeg
	 */
	public $mime_type;
	

}

?>