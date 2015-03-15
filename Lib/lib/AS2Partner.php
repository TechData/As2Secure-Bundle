<?php

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

class AS2Partner {
    // general information
    protected $is_local = false;
    protected $name     = '';
    protected $id       = '';
    protected $email    = '';
    protected $comment  = '';
    
    // security
    protected $sec_pkcs12               = ''; // must contain private/certificate/ca chain
    protected $sec_pkcs12_password      = '';
    
    protected $sec_certificate          = ''; // must contain certificate/ca chain
    
    protected $sec_signature_algorithm  = self::SIGN_SHA1;
    protected $sec_encrypt_algorithm    = self::CRYPT_3DES;
    
    // sending data
    protected $send_compress            = false;
    protected $send_url                 = ''; // full url including "http://" or "https://"
    protected $send_subject             = 'AS2 Message Subject';
    protected $send_content_type        = 'application/EDI-Consent';
    protected $send_credencial_method   = self::METHOD_NONE;
    protected $send_credencial_login    = '';
    protected $send_credencial_password = '';
    protected $send_encoding            = self::ENCODING_BASE64;
    
    // notification process
    protected $mdn_url                  = '';
    protected $mdn_subject                = 'AS2 MDN Subject';
    protected $mdn_request              = self::ACK_SYNC;
    protected $mdn_signed               = true;
    protected $mdn_credencial_method    = self::METHOD_NONE;
    protected $mdn_credencial_login     = '';
    protected $mdn_credencial_password  = '';

    // event trigger connector
    protected $connector_class          = 'AS2Connector';
    
    // 
    protected static $stack = array();
    
    // security methods
    const METHOD_NONE   = 'NONE';
    const METHOD_AUTO   = CURLAUTH_ANY;
    const METHOD_BASIC  = CURLAUTH_BASIC;
    const METHOD_DIGECT = CURLAUTH_DIGEST;
    const METHOD_NTLM   = CURLAUTH_NTLM;
    const METHOD_GSS    = CURLAUTH_GSSNEGOTIATE;
    
    // transfert content encoding
    const ENCODING_BASE64 = 'base64';
    const ENCODING_BINARY = 'binary';
    
    // ack methods
    const ACK_SYNC  = 'SYNC';
    const ACK_ASYNC = 'ASYNC';
    
    // 
    const SIGN_NONE = 'none';
    const SIGN_SHA1 = 'sha1';
    const SIGN_MD5  = 'md5';
    
    // http://www.openssl.org/docs/apps/enc.html#SUPPORTED_CIPHERS
    const CRYPT_NONE    = 'none';
    const CRYPT_RC2_40  = 'rc2-40'; // default
    const CRYPT_RC2_64  = 'rc2-64';
    const CRYPT_RC2_128 = 'rc2-128';
    const CRYPT_DES     = 'des';
    const CRYPT_3DES    = 'des3';
    const CRYPT_AES_128 = 'aes128';
    const CRYPT_AES_192 = 'aes192';
    const CRYPT_AES_256 = 'aes256';

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
        return array('NONE'    => self::CRYPT_NONE,
                     'RC2_40'  => self::CRYPT_RC2_40,
                     'RC2_64'  => self::CRYPT_RC2_64,
                     'RC2_128' => self::CRYPT_RC2_128,
                     'DES'     => self::CRYPT_DES,
                     '3DES'    => self::CRYPT_3DES,
                     'AES_128' => self::CRYPT_AES_128,
                     'AES_192' => self::CRYPT_AES_192,
                     'AES_256' => self::CRYPT_AES_256,
                     );
    }
    
    /**
     * Return an AS2Partner object for a specified Partner ID
     * 
     * @param partner_id   String : Partner ID (case sensitive) corresponds to AS2-To / AS2-From headers
     * @param reload       Boolean : Allow to reload config from file
     * 
     * @return                object : The partner requested
     */
    public static function getPartner($partner_id, $reload = false)
    {
        if ($partner_id instanceof AS2Partner)
            return $partner_id;
        
        $partner_id = trim($partner_id, '"');
        
        // existance file check (caution : Partner name is case sensitive)
        $conf = AS2_DIR_PARTNERS . basename($partner_id) . '.conf';
        if (!file_exists($conf)) throw new AS2Exception('The partner doesn\'t exist : "' . $partner_id . '".');
        
        // get from stack instance
        if (!$reload && isset(self::$stack[$partner_id])){
            return self::$stack[$partner_id];
        }
        
        // loading config file
        $data = array();
        include $conf;
        
        // parse and build object
        if (is_array($data) && count($data)){
            // create new instance
            $partner = new self($data);
            
            // put into stack for fast access
            self::$stack[$partner_id] = $partner;
            return $partner;
        }
        
        // error if not found
        throw new AS2Exception('The partner profile isn\'t correctly loaded.');
    }

    /**
     * Restricted constructor
     * 
     * @param data       The data to set from
     */
    protected function __construct($data)
    {
        // set properties with data
        foreach($data as $key => $value){
            if (!property_exists($this, $key) || is_null($value))
                continue;

            $this->$key = $value;
        }
    }
    
    /**
     * Magic getter
     * 
     * @param key    Property name
     * 
     * @return Return a property of this class
     */
    public function __get($key){
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
    public function __set($key, $value){
        if (property_exists($this, $key))
            $this->$key = $value;
        // for strict processes : throw new Exception if property doesn't exists
    }

    /**
     * Magic method
     * 
     */
    public function __toString(){
        return $this->id;
    }
}
