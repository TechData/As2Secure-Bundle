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

class AS2MDN extends AS2Abstract {
    /**
     * Human readable message
     */
    protected $message = '';

    protected $url = '';

    /**
     * Valid tokens :
     *    
     *    action-mode                    : "manual-action" | "automatic-action"
     *    sending-mode                   : "MDN-sent-manually" | "MDN-sent-automatically"
     *    disposition-type               : "processed" | "failed"
     *    disposition-modifier           : ( "error" | "warning" ) | disposition-modifier-extension
     *    disposition-modifier-extension : (cf : AS2Exception values)
     *    encoded-message-digest         : (base64 format + digest-alg-id = "sha1" | "md5")
     *    reporting-ua                   : user-agent
     */
    protected $attributes = null;

    /**
     * Refers to RFC 4130
     * http://rfclibrary.hosting.com/rfc/rfc4130/rfc4130-34.asp
     */
    const ACTION_AUTO         = 'automatic-action';
    const ACTION_MANUAL       = 'manual-action';
    
    const SENDING_AUTO        = 'MDN-sent-automatically';
    const SENDING_MANUAL      = 'MDN-sent-manually';
    
    const TYPE_PROCESSED      = 'processed';
    const TYPE_FAILED         = 'failed';

    const MODIFIER_ERROR      = 'error';
    const MODIFIER_WARNING    = 'warning';


    public function __construct($data = null, $params = array()){
        
        $this->attributes = new AS2Header(array('action-mode'  => self::ACTION_AUTO,
                                                'sending-mode' => self::SENDING_AUTO));
        
        // adapter
        if (!($data instanceof AS2Exception) && $data instanceof Exception) $data = new AS2Exception($data->getMessage(), 6);
        // full automatic handling
        if ($data instanceof AS2Exception) {
            $this->setMessage($data->getMessage());
            //$this->setHeaders($data->getHeaders());
            $this->setAttribute('disposition-type',     $data->getLevel());
            $this->setAttribute('disposition-modifier', $data->getMessageShort());

            try {$this->setPartnerFrom($params['partner_from']);}
            catch(Exception $e){$this->partner_from = false;}
            try {$this->setPartnerTo($params['partner_to']);}
            catch(Exception $e){$this->partner_to = false;}
        }
        elseif ($data instanceof AS2Request) { // parse response
            $params = array('is_file'      => false,
                            'mimetype'     => 'multipart/report',
                            'partner_from' => $data->getPartnerFrom(),
                            'partner_to'   => $data->getPartnerTo());
            parent::__construct($data->getContent(), $params);

            // check requirements
            if ($this->partner_from->mdn_signed && !$data->isSigned()){
                throw new AS2Exception('MDN from this partner are defined to be signed.', 4);
            }
        }
        elseif ($data instanceof AS2Message){ // standard processed message
            $params['partner_from'] = $data->getPartnerTo();
            $params['partner_to']   = $data->getPartnerFrom();

            parent::__construct(false, $params);
        }
        elseif ($data instanceof Horde_MIME_Part){
            try {$this->setPartnerFrom($params['partner_from']);}
            catch(Exception $e){$this->partner_from = false;}
            try {$this->setPartnerTo($params['partner_to']);}
            catch(Exception $e){$this->partner_to = false;}

            $this->path = AS2Adapter::getTempFilename();
            file_put_contents($this->path, $data->toString(true));

            parent::__construct(false, $params);
        }
        else {
           throw new AS2Exception('Not handled case.');
        }
    }

    /**
     * Return the human readable message
     * 
     * @return string
     */
    public function __toString(){
        return $this->getMessage();
    }

    /**
     * Set the human readable message
     * 
     * @param string $message   The message to set
     */
    public function setMessage($message){
        $this->message = $message;
    }

    /**
     * Return the human readable message
     * 
     * @return string
     */
    public function getMessage(){
        return $this->message;
    }

    /**
     * Set attribute for computer readable message
     * 
     * @param string $key    Token
     * @param string $value  Value
     */
    public function setAttribute($key, $value){
        $this->attributes->addHeader($key, $value);
    }

    /**
     * Return an attribute fromcomputer readable message
     * 
     * @param string  $key    Token
     * 
     * @return string
     */
    public function getAttribute($key){
        return $this->attributes->getHeader($key);
    }

    /**
     * Return the computer readable message
     *
     * @return array 
     */
    public function getAttributes(){
        return $this->attributes->getHeaders();
    }

    /**
     * Encode and generate MDN from attributes and message (if exists)
     * 
     * @param object $message    The refering message
     */
    public function encode($message = null){
        // container
        $container = new Horde_MIME_Part('multipart/report', ' ');

        // first part
        $text = new Horde_MIME_Part('text/plain', $this->getMessage(), MIME_DEFAULT_CHARSET, null, '7bit');
        // add human readable message
        $container->addPart($text);

        // second part
        $lines = new AS2Header();
        $lines->addHeader('Reporting-UA', 'AS2Secure - PHP Lib for AS2 message encoding / decoding');
        if ($this->getPartnerFrom()) {
            $lines->addHeader('Original-Recipient', 'rfc822; "' . $this->getPartnerFrom()->id . '"');
            $lines->addHeader('Final-Recipient', 'rfc822; "' . $this->getPartnerFrom()->id . '"');
        }
        $lines->addHeader('Original-Message-ID', $this->getAttribute('original-message-id'));
        $lines->addHeader('Disposition', $this->getAttribute('action-mode') . '/' . $this->getAttribute('sending-mode') . '; ' . $this->getAttribute('disposition-type'));
        if ($this->getAttribute('disposition-type') != self::TYPE_PROCESSED) {
            $lines->addHeader('Disposition', $lines->getHeader('Disposition') . ': ' . $this->getAttribute('disposition-modifier'));
        }
        if ($this->getAttribute('received-content-mic')) {
            $lines->addHeader('Received-Content-MIC', $this->getAttribute('received-content-mic'));
        }

        // build computer readable message
        $mdn = new Horde_MIME_Part('message/disposition-notification', $lines, MIME_DEFAULT_CHARSET, null, '7bit');
        $container->addPart($mdn);

        $this->setMessageId(self::generateMessageID($this->getPartnerFrom()));

        // headers setup
        $this->headers = new AS2Header(array('AS2-Version'  => '1.0',
                                       'Message-ID'   => $this->getMessageId(),
                                       'Mime-Version' => '1.0',
                                       'Server'       => 'AS2Secure - PHP Lib for AS2 message encoding / decoding',
                                       'User-Agent'   => 'AS2Secure - PHP Lib for AS2 message encoding / decoding',
                                 ));
        $this->headers->addHeaders($container->header());

        if ($this->getPartnerFrom()) {
            $headers_from = array(
                 'AS2-From'                    => '"' . $this->getPartnerFrom()->id . '"',
                 'From'                        => $this->getPartnerFrom()->email,
                 'Subject'                     => $this->getPartnerFrom()->mdn_subject,
                 'Disposition-Notification-To' => $this->getPartnerFrom()->send_url,
            );
            $this->headers->addHeaders($headers_from);
        }

        if ($this->getPartnerTo()) {
            $headers_to = array(
                 'AS2-To'            => '"' . $this->getPartnerTo()->id . '"',
                 'Recipient-Address' => $this->getPartnerTo()->send_url,
            );
            $this->headers->addHeaders($headers_to);
        }

        if ($message && ($url = $message->getHeader('Receipt-Delivery-Option')) && $this->getPartnerFrom()){
            $this->url = $url;
            $this->headers->addHeader('Recipient-Address', $this->getPartnerFrom()->send_url);
        }

        $this->path = AS2Adapter::getTempFilename();
        
        // signing if requested
        if ($message && $message->getHeader('Disposition-Notification-Options')) {
            file_put_contents($this->path, $container->toCanonicalString(true));
            $this->path = $this->adapter->sign($this->path);

            $content = file_get_contents($this->path);
            $this->headers->addHeadersFromMessage($content);

            // TODO : replace with futur AS2MimePart to separate content from header
            if (strpos($content, "\n\n") !== false) $content = substr($content, strpos($content, "\n\n") + 2);
            file_put_contents($this->path, ltrim($content));
        }
        else {
            file_put_contents($this->path, $container->toCanonicalString(false));
            $content = $container->toString();
        }
    }
    
    /**
     * Decode MDN stored into path file and set attributes
     * 
     */
    public function decode(){
        // parse mime message
        $params = array('include_bodies' => true,
                        'decode_headers' => true,
                        'decode_bodies'  => true,
                        'input'          => false,
                        'crlf'           => "\n"
                        );
        $decoder = new Mail_mimeDecode(file_get_contents($this->path));
        $structure = $decoder->decode($params);
        
        // reset values before decoding (for security reasons)
        $this->setMessage('');
        $this->attributes = null;

        // should contains 2 parts
        foreach($structure->parts as $num => $part)
        {
            if (strtolower($part->headers['content-type']) == 'message/disposition-notification')
            {
                // computer readable message
                /*preg_match_all('/([^: ]+): (.+?(?:\r\n\s(?:.+?))*)\r\n/m', $part->body, $headers);
                $headers = array_combine($headers[1], $headers[2]);
                foreach($headers as $key => $val)
                    $this->setAttribute(trim(strtolower($key)), trim($val));*/
                $this->attributes = AS2Header::parseText($part->body);
            }
            else {
                // human readable message
                $this->setMessage(trim($part->body));
            }
        }
    }

    /**
     * Return the url to send message
     * 
     * @return string
     */
    public function getUrl(){
        return $this->url;
    }
}
