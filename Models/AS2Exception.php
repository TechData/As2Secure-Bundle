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

class AS2Exception extends \Exception
{
    /**
     * Refers to RFC 4130
     * http://rfclibrary.hosting.com/rfc/rfc4130/rfc4130-34.asp
     */

    const STATUS_ERROR = 'error';
    const STATUS_FAILURE = 'failure';
    const STATUS_WARNING = 'warning';

    protected static $level_error = array(
        1 => 'authentication-failed',         // the receiver could not authenticate the sender
        2 => 'decompression-failed',          // 
        3 => 'decryption-failed',             // the receiver could not decrypt the message contents
        4 => 'insufficient-message-security', // 
        5 => 'integrity-check-failed',        // the receiver could not verify content integrity
        6 => 'unexpected-processing-error',   // a catch-all for any additional processing errors
    );

    protected static $level_failure = array(
        101 => 'unsupported format',          // sha1, md5
        102 => 'unsupported MIC-algorithms',  // 
    );

    protected static $level_warning = array(
        201 => 'duplicate-document',          // an identical message already exists at the destination server
        202 => 'sender-equals-receiver',      // the AS2-To name is identical to the AS2-From name
    );

    const DEFAULT_ERROR = 6;

    /* -------------------------------------------------- */

    public function __construct($message = '', $code = self::DEFAULT_ERROR, $previous = null)
    {
        if ($previous)
            parent::__construct($message, $code, $previous);
        else
            parent::__construct($message, $code);
    }

    public function getLevel()
    {
        if (in_array($this->code, array_keys(self::$level_error))) return self::STATUS_ERROR;
        if (in_array($this->code, array_keys(self::$level_failure))) return self::STATUS_FAILURE;
        if (in_array($this->code, array_keys(self::$level_warning))) return self::STATUS_WARNING;
        else return self::STATUS_ERROR;
    }

    public function getMessageShort()
    {
        if (in_array($this->code, array_keys(self::$level_error))) return self::$level_error[$this->code];
        if (in_array($this->code, array_keys(self::$level_failure))) return self::$level_failure[$this->code];
        if (in_array($this->code, array_keys(self::$level_warning))) return self::$level_warning[$this->code];
        else return self::$level_error[self::DEFAULT_ERROR];;
    }
}
