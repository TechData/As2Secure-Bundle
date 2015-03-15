<?php
namespace TechData\AS2SecureBundle\Models;
/**
 * AS2Secure - PHP Lib for AS2 message encoding / decoding
 *
 * @author  Sebastien MALOT <contact@as2secure.com>
 *
 * @copyright Copyright (c) 2010, Sebastien MALOT
 *
 * Last release at : {@link http://www.as2secure.com}
 *
 * This file is part of AS2Secure Project.
 *
 * AS2Secure is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * AS2Secure is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with AS2Secure.
 *
 * @license http://www.gnu.org/licenses/lgpl-3.0.html GNU General Public License
 * @version 0.9.0
 *
 */

class Partner
{
    // general information
    const METHOD_NONE = 'NONE';
    const METHOD_AUTO = CURLAUTH_ANY;
    const METHOD_BASIC = CURLAUTH_BASIC;
    const METHOD_DIGECT = CURLAUTH_DIGEST;
    const METHOD_NTLM = CURLAUTH_NTLM;

    // security
    const METHOD_GSS = CURLAUTH_GSSNEGOTIATE; // must contain private/certificate/ca chain
    const ENCODING_BASE64 = 'base64';
    const ENCODING_BINARY = 'binary'; // must contain certificate/ca chain
    const ACK_SYNC = 'SYNC';
    const ACK_ASYNC = 'ASYNC';

    // sending data
    const SIGN_NONE = 'none';
    const SIGN_SHA1 = 'sha1'; // full url including "http://" or "https://"
    const SIGN_MD5 = 'md5';
    const CRYPT_NONE = 'none';
    const CRYPT_RC2_40 = 'rc2-40';
    const CRYPT_RC2_64 = 'rc2-64';
    const CRYPT_RC2_128 = 'rc2-128';
    const CRYPT_DES = 'des';

    // notification process
    const CRYPT_3DES = 'des3';
    const CRYPT_AES_128 = 'aes128';
    const CRYPT_AES_192 = 'aes192';
    const CRYPT_AES_256 = 'aes256';
    protected static $stack = array();
    protected $is_local = false;
    protected $name = '';

    // event trigger connector
    protected $id = '';

    // 
    protected $email = '';

    // security methods
    protected $comment = '';
    protected $sec_pkcs12 = '';
    protected $sec_pkcs12_password = '';
    protected $sec_certificate = '';
    protected $sec_signature_algorithm = self::SIGN_SHA1;
    protected $sec_encrypt_algorithm = self::CRYPT_3DES;

    // transfert content encoding
    protected $send_compress = false;
    protected $send_url = '';

    // ack methods
    protected $send_subject = 'AS2 Message Subject';
    protected $send_content_type = 'application/EDI-Consent';

    // 
    protected $send_credencial_method = self::METHOD_NONE;
    protected $send_credencial_login = '';
    protected $send_credencial_password = '';

    // http://www.openssl.org/docs/apps/enc.html#SUPPORTED_CIPHERS
    protected $send_encoding = self::ENCODING_BASE64;
    protected $mdn_url = ''; // default
    protected $mdn_subject = 'AS2 MDN Subject';
    protected $mdn_request = self::ACK_SYNC;
    protected $mdn_signed = true;
    protected $mdn_credencial_method = self::METHOD_NONE;
    protected $mdn_credencial_login = '';
    protected $mdn_credencial_password = '';
    protected $connector_class = 'AS2Connector';

    /**
     * Restricted constructor
     *
     * @param data       The data to set from
     */
    public function __construct($data)
    {
        // set properties with data
        foreach ($data as $key => $value) {
            if (!property_exists($this, $key) || is_null($value))
                continue;

            $this->$key = $value;
        }
    }


    /**
     * Return the list of available signatures
     *
     * @return array
     */
    public static function getAvailablesSignatures()
    {
        return array('NONE' => self::SIGN_NONE,
            'SHA1' => self::SIGN_SHA1,
        );
    }

    /**
     * Return the list of available cypher
     *
     * @return array
     */
    public static function getAvailablesEncryptions()
    {
        return array('NONE' => self::CRYPT_NONE,
            'RC2_40' => self::CRYPT_RC2_40,
            'RC2_64' => self::CRYPT_RC2_64,
            'RC2_128' => self::CRYPT_RC2_128,
            'DES' => self::CRYPT_DES,
            '3DES' => self::CRYPT_3DES,
            'AES_128' => self::CRYPT_AES_128,
            'AES_192' => self::CRYPT_AES_192,
            'AES_256' => self::CRYPT_AES_256,
        );
    }

    /**
     * Magic getter
     *
     * @param key    Property name
     *
     * @return Return a property of this class
     */
    public function __get($key)
    {
        if (property_exists($this, $key))
            return $this->$key;
        else
            return null; // for strict processes : throw new Exception
    }

    /**
     * Magic setter
     *
     * @param key      Property name
     * @param value    New value to set
     *
     */
    public function __set($key, $value)
    {
        if (property_exists($this, $key))
            $this->$key = $value;
        // for strict processes : throw new Exception if property doesn't exists
    }

    /**
     * Magic method
     *
     */
    public function __toString()
    {
        return $this->id;
    }
}
