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
use TechData\AS2SecureBundle\Factories\Partner as PartnerFacotry;
use TechData\AS2SecureBundle\Factories\Adapter as AdapterFactory;

abstract class AbstractBase
{
    // Injected Services
    protected $adapter = null;

    // Properties
    protected $filename = null;
    protected $mimetype = null;
    protected $path = null;
    protected $files = array();
    protected $headers = null;
    protected $message_id = '';
    protected $is_signed = false;
    protected $is_crypted = false;
    protected $partner_from = null;
    protected $partner_to = null;
    
    /**
     * @var PartnerFacotry
     */
    private $partnerFactory;
    
    /**
     * @var AdapterFactory
     */
    private $adapterFactory;
    
    protected static function generateMessageID($partner)
    {
        if ($partner instanceof Partner) $id = $partner->id;
        else $id = 'unknown';
        return '<' . uniqid('', true) . '@' . round(microtime(true)) . '_' . str_replace(' ', '', strtolower($id) . '_' . php_uname('n')) . '>';
    }

    public function getPath()
    {
        return $this->path;
    }

    public function addFile($file)
    {
        $this->files[] = realpath($file);
    }


    // partner handle

    public function getFiles()
    {
        return $this->files;
    }

    public function getFileName()
    {
        return $this->filename;
    }

    public function getContent()
    {
        return file_get_contents($this->path);
    }

    public function getHeaders()
    {
        return $this->headers;
    }

    // message properties

    public function setHeaders($headers)
    {
        $this->headers = $headers;
    }

    public function getHeader($token)
    {
        return $this->headers->getHeader($token);
    }

    public function getAuthentication()
    {
        return array('method' => Partner::METHOD_NONE,
            'login' => '',
            'password' => '');
    }

    public function getMessageId()
    {
        return $this->message_id;
    }

    public function setMessageId($id)
    {
        $this->message_id = $id;
    }

    public function isCrypted()
    {
        return $this->is_crypted;
    }

    public function isSigned()
    {
        return $this->is_signed;
    }

    public function encode()
    {
        // TODO
    }

    public function decode()
    {
        // TODO
    }

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
    public function getAdapterFactory() {
        return $this->adapterFactory;
    }

    public function setAdapterFactory(AdapterFactory $adapterFactory) {
        $this->adapterFactory = $adapterFactory;
    }

        final protected function initializeBase($data, $params = array())
    {
        if (is_null($this->headers))
            $this->headers = new Header();

        if (is_array($data)) {
            $this->path = $data;
        } elseif ($data) {
            // do nothing
            // content : default is file
            if (isset($params['is_file']) && $params['is_file'] === false) {
                $file = Adapter::getTempFilename();
                file_put_contents($file, $data);
                $this->path = $file;

                // filename
                if (isset($params['filename']))
                    $this->filename = $params['filename'];
            } else {
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

    public function getPartnerFrom()
    {
        return $this->partner_from;
    }

    public function setPartnerFrom($partner_from)
    {
        $this->partner_from = $this->getPartnerFactory()->getPartner($partner_from);
    }

    public function getPartnerTo()
    {
        return $this->partner_to;
    }

    public function setPartnerTo($partner_to)
    {
        $this->partner_to = $this->getPartnerFactory()->getPartner($partner_to);
    }
}
