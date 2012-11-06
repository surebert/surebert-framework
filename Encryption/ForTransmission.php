<?php
/**
 * Interface for encrypting and decrypting data for network transmission
 * @author paul.visco@roswellpark.org
 * @package Encrytion
<code>

$encryptor = new \sb\Encryption\ForTransmission('My very secret key');
//encrypt data
$encrypted_data = $encryptor->encrypt('data to encrypt');

//unecrypt data
$plain_text = $encryptor>decrypt($encrypted_data);

</code>
 */
namespace sb\Encryption;

class ForTransmission
{
    protected $cypher = 'rijndael-256';
    protected $mode = 'ofb';
    protected $key;

    /**
     * Sets the key used for encryption
     * @param $key  String of any length, longer is better
     */
    public function __construct($key)
    {

        if (empty($key)) {
            throw(new \Exception("Cannot use empty key for encryption"));
        }

           $this->key = md5($key);
    }

    /**
     * Encrypts a string
     * @param $string The string of data to encrypt
     */
    public function encrypt($string)
    {
        $td = mcrypt_module_open($this->cypher, '', $this->mode, '');
        $iv = mcrypt_create_iv(mcrypt_enc_get_iv_size($td), mcrypt_RAND);
        mcrypt_generic_init($td, $this->key, $iv);
        $encrypted = mcrypt_generic($td, $string);
        mcrypt_generic_deinit($td);

        return $iv.$encrypted;
    }

    /**
     * Decrypts a string
     * @param $string The data to decrypt
     */
    public function decrypt($string)
    {

        $decrypted = "";
        $td = mcrypt_module_open($this->cypher, '', $this->mode, '');
        $ivsize = mcrypt_enc_get_iv_size($td);
        $iv = substr($string, 0, $ivsize);
        $string = substr($string, $ivsize);
        if ($iv) {
            mcrypt_generic_init($td, $this->key, $iv);
            $decrypted = mdecrypt_generic($td, $string);
        }

        return rtrim($decrypted);
    }
}

