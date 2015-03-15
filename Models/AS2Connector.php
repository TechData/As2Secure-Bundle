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

class AS2Connector {
    const STATUS_OK     = 'OK';
    const STATUS_ERROR  = 'ERROR';

    public static function onReceivedMDN($from, $to, $status, $mdn)
    {
        //echo 'onReceivedMDN('.$from.','.$to.','.$status.')'."\n";
    }

    public static function onReceivedMessage($from, $to, $status, $message)
    {
        //echo 'onReceivedMessage('.$from.','.$to.','.$status.')'."\n";
    }

    public static function onSentMDN($from, $to, $status, $mdn)
    {
        //echo 'onSentMDN('.$from.','.$to.','.$status.')'."\n";
    }

    public static function onSentMessage($from, $to, $status, $message)
    {
        //echo 'onSendMessage('.$from.','.$to.','.$status.')'."\n";
    }
}
