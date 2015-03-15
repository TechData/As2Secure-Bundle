<?php

include 'AS2Header.php';

$data = array('CONTENT_LENGTH' => '4022',
'CONTENT_TYPE' => 'application/pkcs7-mime; smime-type=enveloped-data; name=smime.p7m',
'DOCUMENT_ROOT' => '/home/national/public_html',
'GATEWAY_INTERFACE' => 'CGI/1.1',
'HTTP_ACCEPT_ENCODING' => 'deflate, gzip, x-gzip, compress, x-compress',
'HTTP_AS2_FROM' => 'GISEDI',
'HTTP_AS2_TO' => 'GWT',
'HTTP_AS2_VERSION' => '1.2',
'HTTP_CONNECTION' => 'TE',
'HTTP_CONTENT_DISPOSITION' => 'attachment; filename="smime.p7m"',
'HTTP_DATE' => 'Mon, 11 Oct 2010 18:30:52 GMT',
'HTTP_DISPOSITION_NOTIFICATION_OPTIONS' => 'signed-receipt-protocol=optional, pkcs7-signature; signed-receipt-micalg=optional, sha1',
'HTTP_DISPOSITION_NOTIFICATION_TO' => 'http://63.241.244.12:5080/b2bhttp/inbound/as2',
'HTTP_EDIINT_FEATURES' => 'CEM, multiple-attachments, AS2-Reliability HTTP_FROM: us-mis-edion-call@essilorusa.com HTTP_HOST: ngc.gwpunchout.com',
'HTTP_MESSAGE_ID' => '<CLEOAS2-20101011_183052314@GISEDI_GWT.dallas-gisedi-B>',
'HTTP_MIME_VERSION' => '1.0 HTTP_RECIPIENT_ADDRESS: http://ngc.gwpunchout.com:80/as2secure/www/server.php',
'HTTP_SUBJECT' => 'AS2 Message',
'HTTP_TE' => 'trailers, deflate, gzip, compress',
'HTTP_USER_AGENT' => 'RPT-HTTPClient/0.3-3I (Windows 2003)',
'PATH' => '/bin:/usr/bin',
'PHP_AUTH_PW' => 'GISEDI#2010',
'PHP_AUTH_USER' => 'GISEDI',
'QUERY_STRING' => '',
'REDIRECT_STATUS' => '200',
'REMOTE_ADDR' => '63.241.244.12',
'REMOTE_PORT' => '1876',
'REQUEST_METHOD' => 'POST',
'REQUEST_URI' => '/as2secure/www/server.php',
'SCRIPT_FILENAME' => '/home/national/public_html/as2secure/www/server.php',
'SCRIPT_NAME' => '/as2secure/www/server.php',
'SERVER_ADDR' => '174.123.106.8',
'SERVER_ADMIN' => 'webmaster@ngc.gwpunchout.com',
'SERVER_NAME' => 'ngc.gwpunchout.com SERVER_PORT: 80 SERVER_PROTOCOL: HTTP/1.1 SERVER_SIGNATURE: <address>Apache/2.2.14 (Unix) mod_ssl/2.2.14 OpenSSL/0.9.8i DAV/2 mod_auth_passthrough/2.1 mod_bwlimited/1.4 FrontPage/5.0.2.2635 Server at ngc.gwpunchout.com Port 80</address>',
'SERVER_SOFTWARE' => 'Apache/2.2.14 (Unix) mod_ssl/2.2.14 OpenSSL/0.9.8i DAV/2 mod_auth_passthrough/2.1 mod_bwlimited/1.4 FrontPage/5.0.2.2635',
'UNIQUE_ID' => 'TLNX2q57agIAAFOzJywAAAAG',
'PHP_SELF' => '/as2secure/www/server.php',
'REQUEST_TIME' => '1286821850',
'argv' => 'Array',
'argc' => '0');

$headers = array();

foreach($data as $key => $value){
    if (strpos($key, 'HTTP_') === 0){
        // 5 is to remove 'HTTP_'
        $key = str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($key, 5)))));
        $headers[$key] = $value;
    }
}

var_dump($headers);
