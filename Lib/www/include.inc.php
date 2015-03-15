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
require_once '../lib/AS2Header.php';
require_once '../lib/AS2Connector.php';
require_once '../lib/AS2Partner.php';
require_once '../lib/AS2Abstract.php';
require_once '../lib/AS2Exception.php';
require_once '../lib/AS2Adapter.php';
require_once '../lib/AS2Client.php';
require_once '../lib/AS2Message.php';
require_once '../lib/AS2MDN.php';
require_once '../lib/AS2Request.php';
require_once '../lib/AS2Server.php';

define('AS2_DIR_PARTNERS', '../partners/');
define('AS2_DIR_LOGS',     '../logs/');
define('AS2_DIR_MESSAGES', '../messages/');
define('AS2_DIR_BIN', '../bin/');
