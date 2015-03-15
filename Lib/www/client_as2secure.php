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

include_once('include.inc.php');

$params = array('partner_from'  => 'mycompanyAS2',
                'partner_to'    => 'as2secure');

$tmp_file = AS2Adapter::getTempFilename();
file_put_contents($tmp_file, "Hello guys, that's AS2Secure client test.");

$message = new AS2Message(false, $params);
$message->addFile($tmp_file);
$message->encode();

$client = new AS2Client();
$result = $client->sendRequest($message);
var_dump($result['response']);
