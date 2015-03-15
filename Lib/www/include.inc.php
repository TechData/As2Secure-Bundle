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

error_reporting(E_ALL ^ E_NOTICE);

// files found in PEAR::Mail
require_once 'Mail/RFC822.php';
require_once 'Mail/mimeDecode.php';

// Horde framework common files
require_once '../lib/Horde/String.php';
require_once '../lib/Horde/Util.php';
require_once '../lib/Horde/MIME.php';
require_once '../lib/Horde/MIME/Part.php';
require_once '../lib/Horde/MIME/Message.php';
require_once '../lib/Horde/MIME/Structure.php';

// AS2Secure framework
require_once '../lib/AS2Log.php';
require_once '../lib/Header.php';
require_once '../lib/Partner.php';
require_once '../lib/AbstractBase.php';
require_once '../lib/AS2Exception.php';
require_once '../lib/Adapter.php';
require_once '../lib/Client.php';
require_once '../lib/Message.php';
require_once '../lib/MDN.php';
require_once '../lib/Request.php';
require_once '../lib/Server.php';

define('AS2_DIR_PARTNERS', '../partners/');
define('AS2_DIR_LOGS',     '../logs/');
define('AS2_DIR_MESSAGES', '../messages/');
define('AS2_DIR_BIN', '../bin/');
