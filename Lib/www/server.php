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

try {
    if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == 'POST')
        $response = AS2Server::handle();
    else{
        echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">' .
             '<html><head>' .
             '<meta name="description" content="AS2Secure - PHP Lib for AS2 message encoding / decoding: Your EAI partner">' .
             '<meta name="copyright" content="AS2Secure - PHP Lib for AS2">' .
             '<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">' .
             '<title>AS2Secure - PHP Lib for AS2 message encoding / decoding</title>' .
             '</head><body>'.
             '<h2>AS2Secure - PHP Lib for AS2 message</h2>' .
             '&copy; 2010 - <a href="http://www.as2secure.com">AS2Secure</a><br/><br/>' .
             'You have performed an HTTP GET on this URL.<br/>' .
             'To submit an AS2 message, you must POST the message to this URL.' .
             '</body></html>';
    }
}
catch(Exception $e){
    echo 'Exception : '.$e->getMessage();
    throw $e;
}
