<?php

/**
 * Describes an email attachment, when found they are attached to \sb\Email objects
 *
 * @author paul.visco@roswellpark.org
 * @package Email
 */
namespace sb\Email;

class Attachment
{

    /**
     * The extension of the attachment file
     *
     * @var string
     */
    public $extension = '';

    /**
     * The file name of the file attachment e.g. gif, jpg, png, wav, mp3, etc
     *
     * @var string
     */
    public $name = '';

    /**
     * The path to the attachment on the local drive after reading or before writing
     *
     * @var string
     */
    public $filepath = '';

    /**
     * The encoding type of the attchment
     *
     * //when read from \sb\Email_Reader 0 = 7bit, 1= 8bit, 2=binary, 3=base64, 4=quoted prinatble, 5=other
     * //otherwise use full name
     * @var mixed string/integer
     */
    public $encoding = 'base64';

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
    public $type = 5;

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
     * Creates an email attachment
     *
     * <code>
     * $attachment = new \sb\Email\Attachment($filepath, $mime_type);
     * //an instance of \sb\Email
     * $email->add_attachment($attachment);
     *
     *
     * //OR from string/blob data
     * $attachment = new \sb\Email\Attachment();
     * $attachment->contents = $data_from_db;
     * $attachment->mime_type = "image/jpeg";
     * $attachment->name = "picture.jpg";
     * $email->add_attachment($attachment);
     *
     * //if you wish to zip
     * $attachment->zip();
     * </code>
     *
     * @param String $filepath  Optional The path to the file to attach
     * @param String $mime_type Optional The mime type of the file
     *
     */
    public function __construct($filepath = null, $mime_type = null)
    {

        if ($mime_type) {
            $this->mime_type = $mime_type;
        }

        if ($filepath) {
            $this->filepath = $filepath;
            $this->contents = file_get_contents($filepath);
            $this->name = \basename($filepath);
            \sb\Files::fileToMime($this->filepath);
        }
    }

    /**
     * Zips the attachment before sending and sets the proper mime type
     */
    public function zip()
    {

        if (empty($this->filepath)) {
            throw(new \Exception("Must set \sb\Email\Attachment::file_path before zipping attachment"));
        }

        if (empty($this->name)) {
            $this->name = basename($this->filepath);
        }

        $zip = new \ZipArchive();
        $zipfile_path = \ROOT . "/private/cache/compression/" . md5(microtime(true)) . '_' . $this->name . ".zip";
        $zip_dir = dirname($zipfile_path);
        if (!is_dir($zip_dir) && !@mkdir($zip_dir, 0755, true)) {
            throw(new \Exception("Cannot create /private/cache/compression directory\n"));
        }

        if ($zip->open($zipfile_path, \ZIPARCHIVE::CREATE) !== true) {
            throw(new \Exception("Cannot open <$zipfile_path>\n"));
        }

        $zip->addFromString($this->name, \file_get_contents($this->filepath));

        $zip->close();

        $this->name .= '.zip';

        //this is the content, in this case I am ready the blob data from a saved image file but you could easily replace this with blob data from a database.  The mime type will be added based on the extension using \sb\Files::extensionToMime.  For bizarre mime-types that are not in \sb\Files::extensionToMime you can override this by setting the mime-type manually $myAttachment->mime_type ='bizarre/weird';
        $this->contents = \file_get_contents($zipfile_path);

        //remove the tmp zip file
        \unlink($zipfile_path);

        //add mime type for zip files, \sb\Files should handle this after 1.41
        $this->mime_type = 'application/x-zip-compressed';
    }

    /**
     * Encrypts the extension with PGP using gpg extension for php http://pecl.php.net/package/gnupg
     * @author James Buczkowski, Paul Visco
     * @param string $pgpEncrypt_key The key to use to encrypt
     * @param string $gnupg_path      Optional The path to your .gnupg directory, must be
     * readible by apache, by default served out of /private/resources e.g. ROOT.'/private/resources/.gnupg
     */
    public function pgpEncrypt($pgpEncrypt_key, $gnupg_path = '')
    {

        if (empty($gnupg_path)) {
            $gnupg_path = \ROOT . '/private/resources/.gnupg';
        }

        if (!is_dir($gnupg_path)) {
            throw(new \Exception('In order to use pgp engryption you must either '
                .'pass a valid .gnupg path as the second argument of ' 
                . __METHOD__ . ' or have the .gnupg directory reside in the /private/resources'));
        }

        putenv("GNUPGHOME=" . $gnupg_path);

        $gpg = new \gnupg();
        // throw exception if error occurs
        $gpg->seterrormode(gnupg::ERROR_EXCEPTION);

        $gpg->addencryptkey($pgpEncrypt_key);

        $this->contents = $gpg->encrypt($this->contents);
        $this->mime_type = 'application/pgp';
        $this->name .= '.pgp';
    }

    /**
     * Sets the encoding type of the attachment
     *
     * @param string $encoding either 0-5 or the actual strings
     * 7bit, 8bit, binary, base64, quoted prinatble
     */
    public function setEncoding($encoding)
    {

        $this->encoding = $encoding;
    }
}

