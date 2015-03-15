<?php

namespace TechData\AS2SecureBundle\Interfaces;

/**
 *
 * @author wpigott
 */
interface PartnerProvider {

    /**
     * 
     * @param string $partnerId     Partner ID (case sensitive) corresponds to AS2-To / AS2-From headers
     * @param boolean $reload       Allow to reload config from file
     * @return stdClass             Key/value pair for the partner. See below for details.
    // general information
    protected $is_local = false;
    protected $name     = '';
    protected $id       = '';
    protected $email    = '';
    protected $comment  = '';
    
    // security
    protected $sec_pkcs12               = ''; // must contain private/certificate/ca chain  (contents of file)
    protected $sec_pkcs12_password      = '';
    
    protected $sec_certificate          = ''; // must contain certificate/ca chain (contents of file)
    
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
     */
    public function getPartner($partnerId, $reload = FALSE);
}
