<?php

namespace TechData\AS2SecureBundle\Models;

/**
 * AS2Secure - PHP Lib for AS2 message encoding / decoding
 *
 * @author    Sebastien MALOT <contact@as2secure.com>
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
 * @license   http://www.gnu.org/licenses/lgpl-3.0.html GNU General Public License
 * @version   0.9.0
 *
 */

use TechData\AS2SecureBundle\Factories\Request as RequestFactory;

/**
 * Class Client
 *
 * @package TechData\AS2SecureBundle\Models
 */
class Client
{
    /**
     * @var RequestFactory
     */
    private $requestFactory;
    /**
     * @var array
     */
    protected $response_headers = [];
    /**
     * @var string
     */
    protected $response_content = '';
    /**
     * @var int
     */
    protected $response_indice = 0;
    
    /**
     * Client constructor.
     *
     * @param RequestFactory $requestFactory
     */
    public function __construct(RequestFactory $requestFactory)
    {
        $this->requestFactory = $requestFactory;
    }
    
    /**
     * Send request to the partner (manage headers, security, ...)
     *
     * @param AbstractBase $request The request to send (instanceof : AS2Message | AS2MDN)
     *
     * @return array
     */
    public function sendRequest($request)
    {
        if (!$request instanceof Message && !$request instanceof MDN){
            throw new AS2Exception('Unexpected format');
        }
        // format headers
        $headers = $request->getHeaders()->toFormattedArray();
        
        // initialize variables for building response headers with curl
        $this->response_headers = [];
        $this->response_content = '';
        $this->response_indice  = 0;
        $log = "Header: " . implode(',', $headers) . PHP_EOL;
        // send as2 message with headers
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $request->getUrl());
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_BINARYTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_FRESH_CONNECT, 1);
        curl_setopt($ch, CURLOPT_FORBID_REUSE, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $request->getContent());
        curl_setopt($ch, CURLOPT_USERAGENT, 'AS2Secure - PHP Lib for AS2 message encoding / decoding');
        curl_setopt($ch, CURLOPT_HEADERFUNCTION, [
            $this,
            'handleResponseHeader'
        ]);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $log .= " Content:" . $request->getContent();
        // authentication setup
        $auth = $request->getAuthentication();
        if ($auth['method'] != Partner::METHOD_NONE) {
            curl_setopt($ch, CURLOPT_HTTPAUTH, $auth['method']);
            curl_setopt($ch, CURLOPT_USERPWD, urlencode($auth['login']) . ':' . urlencode($auth['password']));
        }
        $response = curl_exec($ch);
        $info     = curl_getinfo($ch);
        $error    = curl_error($ch);
        curl_close($ch);
        $this->response_content = $response;
        
        $log .= " Response code:" . $info['http_code'];
        
        if ($request instanceof Message && $request->getPartnerTo()->mdn_request == Partner::ACK_SYNC) {
            $temp_response = $this->requestFactory->build($response, new Header($this->response_headers[count($this->response_headers) - 1]));
            $as2_response  = $temp_response->getObject();
            $as2_response->decode();
        }
        else {
            $as2_response = null;
        }
        
        return [
            'request'  => $request,
            'headers'  => ($this->response_headers) ? $this->response_headers[count($this->response_headers) - 1] : $this->response_headers,
            'response' => $as2_response,
            'info'     => $info,
            'error'    => $error,
            'log'      => $log
        ];
    }
    
    /**
     * Return the last request : headers/content
     *
     * @return array
     */
    public function getLastResponse()
    {
        return [
            'headers' => $this->response_headers[count($this->response_headers) - 1],
            'content' => $this->response_content
        ];
    }
    
    /**
     * Allow to retrieve HTTP headers even if there is HTTP redirections
     *
     * @param object $curl   The curl instance
     * @param string $header The header received
     *
     * @return int              The length of current received header
     */
    protected function handleResponseHeader($curl, $header)
    {
        if (!trim($header) && isset($this->response_headers[$this->response_indice]) && count($this->response_headers[$this->response_indice])) {
            $this->response_indice++;
        }
        else {
            $pos = strpos($header, ':');
            if ($pos !== false)
                $this->response_headers[$this->response_indice][trim(strtolower(substr($header, 0, $pos)))] = trim(substr($header, $pos + 1));
        }
        
        return strlen($header);
    }
}
