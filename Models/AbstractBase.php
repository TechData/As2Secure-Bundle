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
use TechData\AS2SecureBundle\Factories\Partner as PartnerFacotry;
use TechData\AS2SecureBundle\Factories\Adapter as AdapterFactory;

/**
 * Class AbstractBase
 *
 * @package TechData\AS2SecureBundle\Models
 */
abstract class AbstractBase
{
    // Injected Services
    /**
     * @var null
     */
    protected $adapter = null;
    
    // Properties
    /**
     * @var null
     */
    protected $filename = null;
    /**
     * @var null
     */
    protected $mimetype = null;
    /**
     * @var null
     */
    protected $path = null;
    /**
     * @var array
     */
    protected $files = [];
    /**
     * @var null
     */
    protected $headers = null;
    /**
     * @var string
     */
    protected $message_id = '';
    /**
     * @var bool
     */
    protected $is_signed = false;
    /**
     * @var bool
     */
    protected $is_crypted = false;
    /**
     * @var null
     */
    protected $partner_from = null;
    /**
     * @var null
     */
    protected $partner_to = null;
    
    /**
     * @var PartnerFacotry
     */
    private $partnerFactory;
    
    /**
     * @var AdapterFactory
     */
    private $adapterFactory;
    
    /**
     * @param $partner
     *
     * @return string
     */
    protected static function generateMessageID($partner)
    {
        if ($partner instanceof Partner)
            $id = $partner->id;
        else $id = 'unknown';
        
        return '<' . uniqid('', true) . '@' . round(microtime(true)) . '_' . str_replace(' ', '', strtolower($id) . '_' . php_uname('n')) . '>';
    }
    
    /**
     * @return null
     */
    public function getPath()
    {
        return $this->path;
    }
    
    /**
     * @param $file
     */
    public function addFile($file)
    {
        $this->files[] = realpath($file);
    }
    
    
    // partner handle
    
    /**
     * @return array
     */
    public function getFiles()
    {
        return $this->files;
    }
    
    /**
     * @return null
     */
    public function getFileName()
    {
        return $this->filename;
    }
    
    /**
     * @return bool|string
     */
    public function getContent()
    {
        return file_get_contents($this->path);
    }
    
    /**
     * @return null
     */
    public function getHeaders()
    {
        return $this->headers;
    }
    
    // message properties
    
    /**
     * @param $headers
     */
    public function setHeaders($headers)
    {
        $this->headers = $headers;
    }
    
    /**
     * @param $token
     *
     * @return mixed
     */
    public function getHeader($token)
    {
        return $this->headers->getHeader($token);
    }
    
    /**
     * @return array
     */
    public function getAuthentication()
    {
        return [
            'method'   => Partner::METHOD_NONE,
            'login'    => '',
            'password' => ''
        ];
    }
    
    /**
     * @return string
     */
    public function getMessageId()
    {
        return $this->message_id;
    }
    
    /**
     * @param $id
     */
    public function setMessageId($id)
    {
        $this->message_id = $id;
    }
    
    /**
     * @return bool
     */
    public function isCrypted()
    {
        return $this->is_crypted;
    }
    
    /**
     * @return bool
     */
    public function isSigned()
    {
        return $this->is_signed;
    }
    
    /**
     *
     */
    public function encode()
    {
        // TODO
    }
    
    /**
     *
     */
    public function decode()
    {
        // TODO
    }
    
    /**
     *
     */
    public function getUrl()
    {
        // TODO
    }
    
    /**
     * @return PartnerFacotry
     */
    
    protected function getPartnerFactory()
    {
        return $this->partnerFactory;
    }
    
    /**
     * @param PartnerFacotry $partnerFactory
     */
    public function setPartnerFactory(PartnerFacotry $partnerFactory)
    {
        $this->partnerFactory = $partnerFactory;
    }
    
    /**
     *
     * @return AdapterFactory
     */
    public function getAdapterFactory()
    {
        return $this->adapterFactory;
    }
    
    /**
     * @param AdapterFactory $adapterFactory
     */
    public function setAdapterFactory(AdapterFactory $adapterFactory)
    {
        $this->adapterFactory = $adapterFactory;
    }
    
    /**
     * @param       $data
     * @param array $params
     *
     * @throws AS2Exception
     */
    final protected function initializeBase($data, $params = [])
    {
        if (is_null($this->headers))
            $this->headers = new Header();
        
        if (is_array($data)) {
            $this->path = $data;
        }
        elseif ($data) {
            // do nothing
            // content : default is file
            if (isset($params['is_file']) && $params['is_file'] === false) {
                $file = Adapter::getTempFilename();
                file_put_contents($file, $data);
                $this->path = $file;
                // filename
                if (isset($params['filename']))
                    $this->filename = $params['filename'];
            }
            else {
                $this->path = $data;
                // filename
                $this->filename = (isset($params['filename']) ? $params['filename'] : basename($this->path));
            }
            
            // mimetype handle
            $this->mimetype = (isset($params['mimetype']) ? $params['mimetype'] : Adapter::detectMimeType($this->path));
        }
        
        // partners
        if (isset($params['partner_from']) && $params['partner_from']) {
            $this->setPartnerFrom($params['partner_from']);
        }
        else throw new AS2Exception('No AS2 From Partner specified.');
        if (isset($params['partner_to']) && $params['partner_to']) {
            $this->setPartnerTo($params['partner_to']);
        }
        else throw new AS2Exception('NO AS2 To Partner specified.');
        
        $this->adapter = $this->getAdapterFactory()->build($this->getPartnerFrom(), $this->getPartnerTo());
    }
    
    /**
     * @return null
     */
    public function getPartnerFrom()
    {
        return $this->partner_from;
    }
    
    /**
     * @param $partner_from
     */
    public function setPartnerFrom($partner_from)
    {
        $this->partner_from = $this->getPartnerFactory()->getPartner($partner_from);
    }
    
    /**
     * @return null
     */
    public function getPartnerTo()
    {
        return $this->partner_to;
    }
    
    /**
     * @param $partner_to
     */
    public function setPartnerTo($partner_to)
    {
        $this->partner_to = $this->getPartnerFactory()->getPartner($partner_to);
    }
}
