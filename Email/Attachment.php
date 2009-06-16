<?php
/**
 * Describes an email attachment, when found they are attached to sb_Email objects
 *
 * @author: Paul Visco
 * @package: email
 * @version 2.1 07/09/2007 06/16/09
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

    /**
     * Zips the attachment before sending and sets the proper mime type
     */
    public function zip(){

        if(empty($this->filepath)){
            throw(new Exception("Must set sb_Email_Attachment::file_path before zipping attachment"));
        }

        if(empty($this->name)){
            $this->name = basename($this->filepath);
        }

        $zip = new ZipArchive();
        $zipfile_path = ROOT."/private/cache/compression/".md5(microtime(true)).'_'.$this->name.".zip";

        @mkdir(dirname($zipfile_path), 0755, true);
        if ($zip->open($zipfile_path, ZIPARCHIVE::CREATE)!==TRUE) {
            throw(new Exception("cannot open <$zipfile_path>\n"));
        }

        $zip->addFromString($this->name, file_get_contents($this->filepath));

        $zip->close();

        $this->name .= '.zip';

         //this is the content, in this case I am ready the blob data from a saved image file but you could easily replace this with blob data from a database.  The mime type will be added based on the extension using sb_Files::extension_to_mime.  For bizarre mime-types that are not in sb_Files::extension_to_mime you can override this by setting the mime-type manually $myAttachment->mime_type ='bizarre/weird';
        $this->contents = file_get_contents($zipfile_path);

        //remove the tmp zip file
        unlink($zipfile_path);

        //add mime type for zip files, sb_Files should handle this after 1.41
        $this->mime_type ='application/x-zip-compressed';

    }


}
?>