<?php

namespace sb\Password;

/*
 * Password Hashing With PBKDF2 (http://crackstation.net/hashing-security.htm).
 * Copyright (c) 2013, Taylor Hornby
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without 
 * modification, are permitted provided that the following conditions are met:
 *
 * 1. Redistributions of source code must retain the above copyright notice, 
 * this list of conditions and the following disclaimer.
 *
 * 2. Redistributions in binary form must reproduce the above copyright notice,
 * this list of conditions and the following disclaimer in the documentation 
 * and/or other materials provided with the distribution.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" 
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE 
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE 
 * ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE 
 * LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR 
 * CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF 
 * SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS 
 * INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN 
 * CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) 
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE 
 * POSSIBILITY OF SUCH DAMAGE.
 */

/*
 * This implementation of Pbkdf2 password hashing
 * Based on https://crackstation.net/hashing-security.htm#phpsourcecode
 * 
 * Store the hash in the database and use it to validate against the user input
 * @author paul.visco@roswellpark.org
 * 
 * <code>
 * 
 * //create the hash
 * $hash = \sb\Password\Hash::create('password');
 * 
 * //validate the hash on login, etc
 * $valid = \sb\Password\Hash::validate('password', $hash); //true
 * </code>
 */

class Hash {
    
    /**
     * The hashin algorithm to use
     * @var string
     */
    public static $hash_algorithm = "sha512";
    
    /**
     * The numer of iterations to use
     * @var int
     */
    public static $iterations = 10000;
    
    /**
     * The salt byte size
     * @var type 
     */
    public static $salt_byte_size = 30;
    
    /**
     * The hash bute size
     * @var type 
     */
    public static $hash_byte_size = 30;
    
    /**
     * Hash algorithm index
     * @var int 
     */
    public static $hash_alogrithm_index = 0;
    
    /**
     * Hash iteration index
     * @var int
     */
    public static $hash_iteration_index= 1;
    
    /**
     * Hash salt index
     * @var int
     */
    public static $hash_salt_index = 2;
    
    /**
     * Hash pdkdf2 index
     * @var int
     */
    public static $hash_pbkdf2_index = 3;

    /**
     * Creates the password hash that you would store for comparison.
     * Make sure to check the size of the output when creating database tables
     * to store this hash.  The defaults produce a 94 char string.  Changing the
     * public static properties may affect the size of the hash returned.
     * 
     * @param string $password
     * @return string The hash
     * <code>
     * $hash = \sb\Password\Hash::create('password');
     * </code>
     */
    public static function create($password) {
        // format: algorithm:iterations:salt:hash
        if(function_exists('random_bytes')){
            $salt = random_bytes(self::$salt_byte_size);
        } else if(function_exists('openssl_random_pseudo_bytes')){
            $salt = openssl_random_pseudo_bytes(self::$salt_byte_size);
        } else if(function_exists('mcrypt_create_iv')){
            $salt = mcrypt_create_iv(self::$salt_byte_size, MCRYPT_DEV_URANDOM);
        } else {
            throw(new Exception("You must have random_bytes, openssl_random_pseudo_bytes or mcrypt_create_iv function available to create salt"));
        }
        
        $salt = base64_encode($salt);
        return self::$hash_algorithm . ":" . self::$iterations . ":" . $salt . ":" .
            base64_encode(self::calculate(
                    self::$hash_algorithm, $password, $salt, self::$iterations, self::$hash_byte_size, true
        ));
    }

    /**
     * Validates a password against its precalculated hash
     * @param string $password
     * @param string $hash
     * @return boolean
     * <code>
     * $bool = \sb\Password\Hash::validate('password', $hash_from_create);
     * </code>
     */
    public static function validate($password, $hash) {
        $params = explode(":", $hash);
        if (count($params) < 4)
            return false;
        $pbkdf2 = base64_decode($params[self::$hash_pbkdf2_index]);
        return self::slowEquals(
                $pbkdf2, self::calculate(
                    $params[self::$hash_alogrithm_index], $password, $params[self::$hash_salt_index], (int) $params[self::$hash_iteration_index], strlen($pbkdf2), true
                )
        );
    }

    /*
     * PBKDF2 key derivation function as defined by RSA's PKCS #5: https://www.ietf.org/rfc/rfc2898.txt
     * $algorithm - The hash algorithm to use. Recommended: SHA256
     * $password - The password.
     * $salt - A salt that is unique to the password.
     * $count - Iteration count. Higher is better, but slower. Recommended: At least 1000.
     * $key_length - The length of the derived key in bytes.
     * $raw_output - If true, the key is returned in raw binary format. Hex encoded otherwise.
     * Returns: A $key_length-byte key derived from the password and salt.
     *
     * Test vectors can be found here: https://www.ietf.org/rfc/rfc6070.txt
     *
     * This implementation of PBKDF2 was originally created by https://defuse.ca
     * With improvements by http://www.variations-of-shadow.com
     */

    protected static function calculate($algorithm, $password, $salt, $count, $key_length, $raw_output = false) {
        $algorithm = strtolower($algorithm);
        if (!in_array($algorithm, hash_algos(), true))
            throw new \Exception('PBKDF2 ERROR: Invalid hash algorithm '.self::$hash_algorithm.'.', E_USER_ERROR);
        if ($count <= 0 || $key_length <= 0)
            throw new \Exception('PBKDF2 ERROR: Invalid parameters.', E_USER_ERROR);

        if (function_exists("hash_pbkdf2")) {
            // The output length is in NIBBLES (4-bits) if $raw_output is false!
            if (!$raw_output) {
                $key_length = $key_length * 2;
            }
            return hash_pbkdf2($algorithm, $password, $salt, $count, $key_length, $raw_output);
        }

        $hash_length = strlen(hash($algorithm, "", true));
        $block_count = ceil($key_length / $hash_length);

        $output = "";
        for ($i = 1; $i <= $block_count; $i++) {
            // $i encoded as 4 bytes, big endian.
            $last = $salt . pack("N", $i);
            // first iteration
            $last = $xorsum = hash_hmac($algorithm, $last, $password, true);
            // perform the other $count - 1 iterations
            for ($j = 1; $j < $count; $j++) {
                $xorsum ^= ($last = hash_hmac($algorithm, $last, $password, true));
            }
            $output .= $xorsum;
        }

        if ($raw_output){
            return substr($output, 0, $key_length);
        } else {
            return bin2hex(substr($output, 0, $key_length));
        }
    }

    /**
     * Compares two strings $a and $b in length-constant time.
     * @param string $a
     * @param string $b
     * @return boolean
     */
    protected static function slowEquals($a, $b) {
        $diff = strlen($a) ^ strlen($b);
        for ($i = 0; $i < strlen($a) && $i < strlen($b); $i++) {
            $diff |= ord($a[$i]) ^ ord($b[$i]);
        }
        return $diff === 0;
    }

}
