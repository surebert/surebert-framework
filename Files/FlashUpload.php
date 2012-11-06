<?php

/**
 * Models a file uploaded with the flash multifile uploader
 * 
 * You can laos extend this class and add a on_save method which can 
 * handle different file upload types by extension, etc
 * @author paul.visco@roswellpark.org
 * @package Flash
 * 
 */
namespace sb\Files;

class FlashUpload
{

    /**
     * The name of the file uploaded
     * @var string
     */
    public $name;

    /**
     * The file path to the file after it is uploaded
     * @var string
     */
    public $path;

    /**
     * The file extension of the uploaded file
     * @var string
     */
    public $ext;

    /**
     * The filesize of the uploaded file in K
     * @var int
     */
    public $sizeK;

    /**
     * The error given during upload if one occurs
     * @var unknown_type
     */
    public $error;

    /**
     * The uploaded $_FILES['FileData'] file reference
     * @var Array with name, tmp_name, size, error keys
     */
    protected $uploaded_file;

    /**
     * An english description of the PHP upload error codes from the manual, used to throw error
     * @var Array
     */
    public static $upload_errors = Array(
        UPLOAD_ERR_INI_SIZE => 'The uploaded file exceeds the upload_max_filesize.',
        UPLOAD_ERR_FORM_SIZE =>
        'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form.',
        UPLOAD_ERR_PARTIAL => 'The uploaded file was only partially uploaded.',
        UPLOAD_ERR_NO_FILE => 'No file was uploaded.',
        UPLOAD_ERR_NO_TMP_DIR => 'Missing a temporary folder.',
        UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk.',
        UPLOAD_ERR_EXTENSION => 'File upload stopped by extension.',
    );

    /**
     * Instatiates a new file upload, if the file data is stored in any key besides Filedata pass that
     * @param $key
     *
     * <code>
     * $file = new \sb\Flash_FileUpload();
     * $uploaded = $file->save(ROOT.'/public/content/users/paul/'.date('my'));
     * 
     * if($uploaded){
     *    echo json_encode($file);
     * } else {
     *    echo '0';
     * }
     *
     * </code>
     */
    public function __construct($key = 'Filedata')
    {

        if (isset(Gateway::$request->files[$key])) {
            $this->uploaded_file = Gateway::$request->files[$key];
        } else {
            throw(new \Exception('\sb\Gateway::$request->files[' . $key . '] must be set in order to upload'));
        }
    }

    /**
     * Saves the uploaded file to its final desitnation and call on_save to for special processing
     * @param $destination_directory
     * @return boolean true if saved, false if not
     */
    public function save($destination_directory)
    {

        if (!is_dir($destination_directory)) {
            if (!mkdir($destination_directory, 0777, true)) {
                throw(new \Exception('Could not create upload directory'));
            }
        }

        $this->name = \sb\Strings::clean_file_name($this->uploaded_file['name']);

        $this->path = $destination_directory . '/' . $this->name;
        $arr = explode('.', $this->name);
        $ext = array_pop($arr);

        $this->ext = strtolower($ext);
        $this->sizeK = round($this->uploaded_file['size'] / 1000);
        $this->error = $this->uploaded_file['error'];

        if ($this->error && $this->error != UPLOAD_ERR_OK) {
            throw(new \Exception(self::$upload_errors[$this->error]));
        }

        if (!move_uploaded_file($this->uploaded_file['tmp_name'],
            $this->path)) {
            throw(new \Exception(
                'The file could not be moved to its final destination at '
                . $this->path
                ));
        }

        if (method_exists($this, 'on_save')) {
            return $this->on_save();
        } else {
            return true;
        }
    }
}

