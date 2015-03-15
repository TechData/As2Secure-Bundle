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


class AS2Server {
    const TYPE_MESSAGE = 'Message';
    const TYPE_MDN     = 'MDN';

    /**
     * Handle a request (server side)
     * 
     * @param request     (If not set, get data from standard input)
     * 
     * @return request    The request handled
     */
    public static function handle($request = null)
    {
        // handle any problem in case of SYNC MDN process
        ob_start();

        try {
            $error = null;
            
            if (is_null($request)){
                // content loading
                $data = file_get_contents('php://input');
                if (!$data) throw new AS2Exception('An empty AS2 message (no content) was received, the message will be suspended.');
                
                // headers loading
                $headers = AS2Header::parseHttpRequest();
                if (!count($headers)) throw new AS2Exception('An empty AS2 message (no headers) was received, the message will be suspended.');
                
                // check content of headers
                if (!$headers->exists('message-id')) throw new AS2Exception('A malformed AS2 message (no message-id) was received, the message will be suspended.');
                if (!$headers->exists('as2-from'))   throw new AS2Exception('An AS2 message was received that did not contain the AS2-From header.');
                if (!$headers->exists('as2-to'))     throw new AS2Exception('An AS2 message was received that did not contain the AS2-To header.');
    
                // main save action
                $filename = self::saveMessage($data, $headers);
                
                // request building
                $request = new AS2Request($data, $headers); // TODO : implement AS2Header into AS2Request
                
                // warning / notification
                if (trim($headers->getHeader('as2-from')) == trim($headers->getHeader('as2-to'))) AS2Log::warning($headers->getHeader('message-id'), 'The AS2-To name is identical to the AS2-From name.');
                // log some informations
                AS2Log::info($headers->getHeader('message-id'), 'Incoming transmission is a AS2 message, raw message size: ' . round(strlen($data)/1024, 2) . ' KB.');
                
                // try to decrypt data
                $decrypted = $request->decrypt();
                // save data if encrypted
                if ($decrypted) {
                    $content = file_get_contents($decrypted);
                    self::saveMessage($content, array(), $filename.'.decrypted', 'decrypted');
                }
            }
            elseif (!$request instanceof AS2Request){
                throw new AS2Exception('Unexpected error occurs while handling AS2 message : bad format');
            }
            else {
                $headers = $request->getHeaders();
            }
            
            $object = $request->getObject();
        }
        catch(Exception $e){
            // get error while handling request
            $error = $e;
            //throw $e;
        }
        
        //
        $mdn = null;

        if ($object instanceof AS2Message || (!is_null($error) && !($object instanceof AS2MDN))){
            $object_type = self::TYPE_MESSAGE;
            AS2Log::info(false, 'Incoming transmission is a Message.');
            
            try {
                if (is_null($error)){
                    $object->decode();
                    $files = $object->getFiles();
                    AS2Log::info(false, count($files) . ' payload(s) found in incoming transmission.');
                    foreach($files as $key => $file){
                        $content = file_get_contents($file['path']);
                        AS2Log::info(false, 'Payload #' . ($key+1) . ' : ' . round(strlen($content) / 1024, 2) . ' KB / "' . $file['filename'] . '".');
                        self::saveMessage($content, array(), $filename . '.payload_'.$key, 'payload');
                    }

                    $mdn = $object->generateMDN($error);
                    $mdn->encode($object);
                }
                else {
                    throw $error;
                }
            }
            catch(Exception $e){
                $params = array('partner_from' => $headers->getHeader('as2-from'),
                                'partner_to'   => $headers->getHeader('as2-to'));
                $mdn = new AS2MDN($e, $params);
                $mdn->setAttribute('original-message-id', $headers->getHeader('message-id'));
                $mdn->encode();
            }
        }
        elseif ($object instanceof AS2MDN) {
            $object_type = self::TYPE_MDN;
            AS2Log::info(false, 'Incoming transmission is a MDN.');
        }
        else {
            AS2Log::error(false, 'Malformed data.');
        }
        
        // call Connector object to handle specific actions
        try {
            if ($request instanceof AS2Request) {
                // build arguments
                $params = array('from'   => $headers->getHeader('as2-from'),
                                'to'     => $headers->getHeader('as2-to'),
                                'status' => '',
                                'data'   => '');
                if ($error) {
                    $params['status'] = AS2Connector::STATUS_ERROR;
                    $params['data']   = array('object'  => $object,
                                              'error'   => $error);
                }
                else {
                    $params['status'] = AS2Connector::STATUS_OK;
                    $params['data']   = array('object'  => $object,
                                              'error'   => null);
                }
            
                // call PartnerTo's connector
                if ($request->getPartnerTo() instanceof AS2Partner) {
                    $connector = $request->getPartnerTo()->connector_class;
                    call_user_func_array(array($connector, 'onReceived' . $object_type), $params);
                }
            
                // call PartnerFrom's connector
                if ($request->getPartnerFrom() instanceof AS2Partner) {
                    $connector = $request->getPartnerFrom()->connector_class;
                    call_user_func_array(array($connector, 'onSent' . $object_type), $params);
                }
            }
        }
        catch(Exception $e) {
            $error = $e;
        }

        // build MDN
        if (!is_null($error) && $object_type == self::TYPE_MESSAGE) {
            $params = array('partner_from' => $headers->getHeader('as2-from'),
                            'partner_to'   => $headers->getHeader('as2-to'));
            $mdn = new AS2MDN($e, $params);
            $mdn->setAttribute('original-message-id', $headers->getHeader('message-id'));
            $mdn->encode();
        }

        // send MDN
        if (!is_null($mdn)){
            if (!$headers->getHeader('receipt-delivery-option')) {
                // SYNC method

                // re-active output data
                ob_end_clean();

                // send headers
                foreach($mdn->getHeaders() as $key => $value) {
                    $header = str_replace(array("\r", "\n", "\r\n"), '', $key . ': ' . $value);
                    header($header);
                }
                
                // output MDN
                echo $mdn->getContent();

                AS2Log::info(false, 'An AS2 MDN has been sent.');
            }
            else {
                // ASYNC method

                // cut connection and wait a few seconds
                self::closeConnectionAndWait(5);

                // delegate the mdn sending to the client
                $client = new AS2Client();
                $result = $client->sendRequest($mdn);
                if ($result['info']['http_code'] == '200'){
                    AS2Log::info(false, 'An AS2 MDN has been sent.');
                }
                else{
                    AS2Log::error(false, 'An error occurs while sending MDN message : ' . $result['info']['http_code']);
                }
            }
        }
        
        return $request;
    }

    /**
     * Save the content of the request for futur handle and/or backup
     * 
     * @param content       The content to save (mandatory)
     * @param headers       The headers to save (optional)
     * @param filename      The filename to use if known (optional)
     * @param type          Values : raw | decrypted | payload (mandatory)
     * 
     * @return       String  : The main filename
     */
    protected static function saveMessage($content, $headers, $filename = '', $type = 'raw'){
        umask(000);

        $dir = AS2_DIR_MESSAGES . '_rawincoming';
        $dir = realpath($dir);
        @mkdir($dir, 0777, true);
        
        if (!$filename) {
            list($micro, ) = explode(' ', microtime());
            $micro = str_pad(round($micro * 1000), 3, '0');
            $host = ($_SERVER['REMOTE_ADDR']?$_SERVER['REMOTE_ADDR']:'unknownhost');
            $filename = date('YmdHis') . $micro . '_' . $host . '.as2';
        }

        switch($type){
            case 'raw':
                file_put_contents($dir . '/' . $filename, $content);
                if (count($headers)){
                    file_put_contents($dir . '/' . $filename . '.header', $headers);
                }
                break;
                
            case 'decrypted':
            case 'payload':
                file_put_contents($dir . '/' . $filename, $content);
                break;
        }

        return $filename;
    }
    
    /**
     * Close current HTTP connection and wait some secons 
     * 
     * @param int $sleep   The number of seconds to wait for
     */
    protected static function closeConnectionAndWait($sleep) {
        // cut connexion and wait a few seconds
        ob_end_clean();
        header("Connection: close\r\n");
        header("Content-Encoding: none\r\n");
        ignore_user_abort(true); // optional
        ob_start();
        $size = ob_get_length();
        header("Content-Length: $size");
        ob_end_flush();     // Strange behaviour, will not work
        flush();            // Unless both are called !
        ob_end_clean();
        session_write_close();
        
        // wait some seconds before sending MDN notification
        sleep($sleep);
    }
}
