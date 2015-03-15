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

class Adapter
{
    /**
     * Allow to specify full path to main applications
     * for overriding PATH usage
     */
    public static $ssl_adapter = 'AS2Secure.jar';
    public static $ssl_openssl = 'openssl';
    public static $javapath = 'java';

    protected $partner_from = null;
    protected $partner_to = null;

    /**
     * Array to store temporary files created and scheduled to unlink
     */
    protected static $tmp_files = null;

    public function __construct($partner_from, $partner_to)
    {
        try {
            $this->partner_from = Partner::getPartner($partner_from);
        } catch (Exception $e) {
            throw new AS2Exception('Sender AS2 id "' . $partner_from . '" is unknown.');
        }

        try {
            $this->partner_to = Partner::getPartner($partner_to);
        } catch (Exception $e) {
            throw new AS2Exception('Receiver AS2 id "' . $partner_to . '" is unknown.');
        }
    }

    /**
     * Generate a mime multipart file from files
     *
     * @param array $input The list of extracted files (path / mimetype / filename)
     *
     * @return string              The file generated
     */
    public function compose($files)
    {
        try {
            if (!is_array($files) || !count($files))
                throw new Exception('No file provided.');

            $args = '';
            foreach ($files as $file) {
                $args .= ' -file ' . escapeshellarg($file['path']) .
                    ' -mimetype ' . escapeshellarg($file['mimetype']) .
                    ' -name ' . escapeshellarg($file['filename']);
            }

            $output = self::getTempFilename();

            // execute main operation
            $command = self::$javapath . ' -jar ' . escapeshellarg(AS2_DIR_BIN . self::$ssl_adapter) .
                ' compose' .
                $args .
                ' -out ' . escapeshellarg($output);

            $result = self::exec($command);

            return $output;
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Extract files from mime multipart file
     *
     * @param string $input The file to extract
     *
     * @return array                The list of extracted files (path / mimetype / filename)
     */
    public function extract($input)
    {
        try {
            $output = self::getTempFilename();

            // execute main operation
            $command = self::$javapath . ' -jar ' . escapeshellarg(AS2_DIR_BIN . self::$ssl_adapter) .
                ' extract' .
                ' -in ' . escapeshellarg($input) .
                ' -out ' . escapeshellarg($output);
            $results = self::exec($command, true);

            // array returned
            $files = array();

            foreach ($results as $tmp) {
                $tmp = explode(';', $tmp);
                if (count($tmp) <= 1) continue;
                if (count($tmp) != 3) throw new AS2Exception('Unexpected data structure while extracting message.');

                $file = array();
                $file['path'] = trim($tmp[0], '"');
                $file['mimetype'] = trim($tmp[1], '"');
                $file['filename'] = trim($tmp[2], '"');
                $files[] = $file;

                // schedule file deletion
                Adapter::addTempFileForDelete($file['path']);
            }

            return $files;
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Compress a file which contains mime headers
     *
     * @param string $input The file to compress
     *
     * @return string                The content compressed
     */
    public function compress($input)
    {
        try {
            $output = self::getTempFilename();

            // execute main operation
            $command = self::$javapath . ' -jar ' . escapeshellarg(AS2_DIR_BIN . self::$ssl_adapter) .
                ' compress' .
                ' -in ' . escapeshellarg($input) .
                ' -out ' . escapeshellarg($output);

            $result = self::exec($command);

            return $output;
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Decompress a file which contains mime headers
     *
     * @param string $input The file to compress
     *
     * @return string                The content decompressed
     */
    public function decompress($input)
    {
        try {
            $output = self::getTempFilename();

            // execute main operation
            $command = self::$javapath . ' -jar ' . escapeshellarg(AS2_DIR_BIN . self::$ssl_adapter) .
                ' decompress' .
                ' -in ' . escapeshellarg($input) .
                ' -out ' . escapeshellarg($output);

            $result = self::exec($command);

            return $output;
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Sign a file which contains mime headers
     *
     * @param string $input The file to sign
     * @param boolean $use_zlib Use Zlib to compress main container
     * @param string $encoding Encoding to use for main container (base64 | binary)
     *
     * @return string                The content signed
     */
    public function sign($input, $use_zlib = false, $encoding = 'base64')
    {
        try {
            if (!$this->partner_from->sec_pkcs12)
                throw new Exception('Config error : PKCS12 (' . $this->partner_from->id . ')');

            $password = ($this->partner_from->sec_pkcs12_password ? ' -password ' . escapeshellarg($this->partner_from->sec_pkcs12_password) : ' -nopassword');

            if ($use_zlib) $compress = ' -compress';
            else $compress = '';

            $output = self::getTempFilename();

            // execute main operation
            $command = self::$javapath . ' -jar ' . escapeshellarg(AS2_DIR_BIN . self::$ssl_adapter) .
                ' sign' .
                ' -pkcs12 ' . escapeshellarg($this->partner_from->sec_pkcs12) .
                $password .
                $compress .
                ' -encoding ' . escapeshellarg(strtolower($encoding)) .
                ' -in ' . escapeshellarg($input) .
                ' -out ' . escapeshellarg($output) .
                ' >/dev/null';
            $result = self::exec($command);

            return $output;
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Verify a signed file
     *
     * @param string $input The file to check
     *
     * @return string                The content signed
     */
    public function verify($input)
    {
        try {
            if ($this->partner_from->sec_pkcs12)
                $security = ' -pkcs12 ' . escapeshellarg($this->partner_from->sec_pkcs12) .
                    ($this->partner_from->sec_pkcs12_password ? ' -password ' . escapeshellarg($this->partner_from->sec_pkcs12_password) : ' -nopassword');
            else
                $security = ' -cert ' . escapeshellarg($this->partner_from->sec_certificate);

            $output = self::getTempFilename();

            $command = self::$javapath . ' -jar ' . escapeshellarg(AS2_DIR_BIN . self::$ssl_adapter) .
                ' verify' .
                $security .
                ' -in ' . escapeshellarg($input) .
                ' -out ' . escapeshellarg($output) .
                ' >/dev/null 2>&1';

            // on error, an exception is throw
            $result = self::exec($command);

            return $output;
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Encrypt a file
     *
     * @param string $input The file to encrypt
     * @param string $cypher The Cypher to use for encryption
     *
     * @return string                The message encrypted
     */
    public function encrypt($input, $cypher = 'des3')
    {
        try {
            if (!$this->partner_to->sec_certificate)
                $certificate = self::getPublicFromPKCS12($this->partner_to->sec_pkcs12, $this->partner_to->sec_pkcs12_password);
            else
                $certificate = $this->partner_to->sec_certificate;

            if (!$certificate) throw new Exception('Unable to extract private key from PKCS12 file. (' . $this->partner_to->sec_pkcs12 . ' - using:' . $this->partner_to->sec_pkcs12_password . ')');

            $output = self::getTempFilename();

            $command = self::$ssl_openssl . ' smime ' .
                ' -encrypt' .
                ' -in ' . escapeshellarg($input) .
                ' -out ' . escapeshellarg($output) .
                ' -des3 ' . escapeshellarg($certificate);
            $result = $this->exec($command);

            $headers = 'Content-Type: application/pkcs7-mime; smime-type="enveloped-data"; name="smime.p7m"' . "\n" .
                'Content-Disposition: attachment; filename="smime.p7m"' . "\n" .
                'Content-Transfer-Encoding: binary' . "\n\n";
            $content = file_get_contents($output);

            // we remove header auto-added by openssl
            $content = substr($content, strpos($content, "\n\n") + 2);
            $content = base64_decode($content);

            $content = $headers . $content;
            file_put_contents($output, $content);

            /*if ($this->partner_to->sec_pkcs12)
                $security = ' -pkcs12 '.escapeshellarg($this->partner_to->sec_pkcs12).
                            ($this->partner_to->sec_pkcs12_password?' -password '.escapeshellarg($this->partner_to->sec_pkcs12_password):' -nopassword');
            else
                $security = ' -cert '.escapeshellarg($this->partner_to->sec_certificate);
            
            $command = self::$javapath.' -jar '.escapeshellarg(AS2_DIR_BIN.self::$ssl_adapter).
                                       ' encrypt'.
                                       $security.
//                                       ' -cypher '.escapeshellarg($cypher).
                                       ' -in '.escapeshellarg($input).
                                       ' -out '.escapeshellarg($output);

            $result = $this->exec($command);*/

            return $output;
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Decrypt a message
     *
     * @param string $input The file to decrypt
     *
     * @return string                The file decrypted
     */
    public function decrypt($input)
    {
        /*file_put_contents('/tmp/decrypt', '---------------------------------------------------------------'."\n", FILE_APPEND);
        file_put_contents('/tmp/decrypt', 'try to decrypt file :'."\n", FILE_APPEND);
        file_put_contents('/tmp/decrypt', '---------------------------------------------------------------'."\n", FILE_APPEND);
        file_put_contents('/tmp/decrypt', print_r(file_get_contents($input), true)."\n", FILE_APPEND);*/

        try {
            $private_key = self::getPrivateFromPKCS12($this->partner_to->sec_pkcs12, $this->partner_to->sec_pkcs12_password, '');
            if (!$private_key)
                throw new AS2Exception('Unable to extract private key from PKCS12 file. (' . $this->partner_to->sec_pkcs12 . ' - using:' . $this->partner_to->sec_pkcs12_password . ')');

            $output = self::getTempFilename();

            $command = self::$ssl_openssl . ' smime ' .
                ' -decrypt ' .
                ' -in ' . escapeshellarg($input) .
                ' -inkey ' . escapeshellarg($private_key) .
                ' -out ' . escapeshellarg($output);

            // seems to generate non-conform message
            /*$security = ' -pkcs12 '.escapeshellarg($this->partner_to->sec_pkcs12).
                ($this->partner_to->sec_pkcs12_password?' -password '.escapeshellarg($this->partner_to->sec_pkcs12_password):' -nopassword');

            $command = self::$javapath.' -jar '.escapeshellarg(AS2_DIR_BIN.self::$ssl_adapter).
                                       ' decrypt'.
                                       $security.
                                       ' -in '.escapeshellarg($input).
                                       ' -out '.escapeshellarg($output).
                                       ' >/dev/null';*/

            $result = $this->exec($command);

            return $output;
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Calculate the message integrity check (MIC) using SHA1 or MD5 algo
     *
     * @param string $input The file to use
     * @param string $algo The algo to use
     *
     * @return string                The hash calculated
     */
    public static function calculateMicChecksum($input, $algo = 'sha1')
    {
        if (strtolower($algo) == 'sha1')
            return base64_encode(self::hex2bin(sha1_file($input))) . ', sha1';
        else
            return base64_encode(self::hex2bin(md5_file($input))) . ', md5';
    }

    /**
     * Extract the message integrity check (MIC) from the digital signature
     *
     * @param string $input The file containing the signed message
     *
     * @return string                The hash extracted
     */
    public static function getMicChecksum($input)
    {
        try {
            $command = self::$javapath . ' -jar ' . escapeshellarg(AS2_DIR_BIN . self::$ssl_adapter) .
                ' checksum' .
                ' -in ' . escapeshellarg($input) .
                ' 2>/dev/null';

            $dump = self::exec($command, true);

            return $dump[0];
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Extract Private Certificate from a PKCS12 Certificate
     *
     * @param string $input The PKCS12 Certificate
     * @param string $password The PKCS12 Certificate's password
     *
     * @return string                The file which contains the Private Certificate
     */
    public static function getPrivateFromPKCS12($input, $password = '')
    {
        return self::getDataFromPKCS12($input, 'pkey', $password);
    }

    /**
     * Extract Public certificate from a PKCS12 Certificate
     *
     * @param string $input The PKCS12 Certificate
     * @param string $password The PKCS12 Certificate's password
     *
     * @return string            The file which contains the Public Certificate
     */
    public static function getPublicFromPKCS12($input, $password = '')
    {
        return self::getDataFromPKCS12($input, 'cert', $password);
    }

    /**
     * Extract CA from a PKCS12 Certificate
     *
     * @param string $input The PKCS12 Certificate
     * @param string $password The PKCS12 Certificate's password
     *
     * @return string            The file which contains the CA
     */
    public static function getCAFromPKCS12($input, $password = '')
    {
        return self::getDataFromPKCS12($input, 'extracerts', $password);
    }

    /**
     * Extract Part from a PKCS12 Certificate
     *
     * @param string $input The PKCS12 Certificate
     * @param string $token The Part to extract
     * @param string $password The PKCS12 Certificate's password
     *
     * @return string            The file which contains the Part
     */
    protected static function getDataFromPKCS12($input, $token, $password = '')
    {
        try {
            $pkcs12 = file_get_contents($input);

            $certs = array();
            openssl_pkcs12_read($pkcs12, $certs, $password);

            if (!isset($certs[$token])) {
                throw new AS2Exception('Unexpected error while extracting certificates from pkcs12 container.');
            }

            $output = self::getTempFilename();
            file_put_contents($output, $certs[$token]);

            return $output;
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Create a temporary file into temporary directory and add it to
     * the garbage collector at shutdown
     *
     * @return string       The temporary file generated
     */
    public static function getTempFilename()
    {
        if (is_null(self::$tmp_files)) {
            self::$tmp_files = array();
            register_shutdown_function(array('Adapter', '_deleteTempFiles'));
        }

        $dir = sys_get_temp_dir();
        $filename = tempnam($dir, 'as2file_');
        self::$tmp_files[] = $filename;
        return $filename;
    }

    /**
     * Schedule file for deletion
     *
     */
    public static function addTempFileForDelete($file)
    {
        if (is_null(self::$tmp_files)) {
            self::$tmp_files = array();
            register_shutdown_function(array('Adapter', '_deleteTempFiles'));
        }
        self::$tmp_files[] = $file;
    }

    /**
     * Garbage collector to delete temp files created with 'self::getTempFilename'
     * with shutdown function
     *
     */
    public static function _deleteTempFiles()
    {
        foreach (self::$tmp_files as $file)
            @unlink($file);
    }

    /**
     * Execute a command line and throw Exception if an error appends
     *
     * @param string $command The command line to execute
     * @param boolean $return_output True  to return all data from standard output
     *                                   False to return only the error code
     *
     * @return string    The error code or the content from standard output
     */
    public static function exec($command, $return_output = false)
    {
        $output = array();
        $return_var = 0;
        try {
            exec($command, $output, $return_var);
            $line = (isset($output[0]) ? $output[0] : 'Unexpected error in command line : ' . $command);
            if ($return_var) throw new Exception($line, (int)$return_var);
        } catch (Exception $e) {
            throw $e;
        }

        if ($return_output)
            return $output;
        else
            return $return_var;
    }

    /**
     * Fix the content type to match with RFC 2311
     *
     * @param string $file The file to fix
     * @param string $type The type to choose to correct (crypt | sign)
     *
     * @return none
     */
    /*protected static function fixContentType($file, $type) {
        if ($type == 'crypt') {
            $from = 'application/x-pkcs7-mime';
            $to = 'application/pkcs7-mime';
        } else {
            $from = 'application/x-pkcs7-signature';
            $to = 'application/pkcs7-signature';
        }
        $content = file_get_contents($file);
        $content = str_replace('Content-Type: ' . $from, 'Content-Type: ' . $to, $content);
        file_put_contents($file, $content);
    }*/

    /**
     * Convert a string from hexadecimal format to binary format
     *
     * @param string $str The string in hexadecimal format.
     *
     * @return string       The string in binary format.
     */
    public static function hex2bin($str)
    {
        $bin = '';
        $i = 0;
        do {
            $bin .= chr(hexdec($str{$i} . $str{($i + 1)}));
            $i += 2;
        } while ($i < strlen($str));
        return $bin;
    }

    /**
     * Determine the mimetype of a file (also called 'Content-Type')
     *
     * @param string $file The file to analyse
     *
     * @return string       The mimetype
     */
    public static function detectMimeType($file)
    {
        // for old PHP (deprecated)
        if (function_exists('mime_content_type'))
            return mime_content_type($file);

        // for PHP > 5.3.0 / PECL FileInfo > 0.1.0
        if (function_exists('finfo_file')) {
            $finfo = finfo_open(FILEINFO_MIME);
            $mimetype = finfo_file($finfo, $file);
            finfo_close($finfo);
            return $mimetype;
        }

        $os = self::detectOS();
        // for Unix OS : command line
        if ($os == 'UNIX') {
            $mimetype = trim(exec('file -b -i ' . escapeshellarg($file)));
            $parts = explode(';', $mimetype);
            return trim($parts[0]);
        }

        // fallback for Windows and Others OS
        // source code found at : 
        // @link http://fr2.php.net/manual/en/function.mime-content-type.php#87856
        $mime_types = array(
            'txt' => 'text/plain',
            'htm' => 'text/html',
            'html' => 'text/html',
            'php' => 'text/html',
            'css' => 'text/css',
            'js' => 'application/javascript',
            'json' => 'application/json',
            'xml' => 'application/xml',
            'swf' => 'application/x-shockwave-flash',
            'flv' => 'video/x-flv',

            // images
            'png' => 'image/png',
            'jpe' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'jpg' => 'image/jpeg',
            'gif' => 'image/gif',
            'bmp' => 'image/bmp',
            'ico' => 'image/vnd.microsoft.icon',
            'tiff' => 'image/tiff',
            'tif' => 'image/tiff',
            'svg' => 'image/svg+xml',
            'svgz' => 'image/svg+xml',

            // archives
            'zip' => 'application/zip',
            'rar' => 'application/x-rar-compressed',
            'exe' => 'application/x-msdownload',
            'msi' => 'application/x-msdownload',
            'cab' => 'application/vnd.ms-cab-compressed',

            // audio/video
            'mp3' => 'audio/mpeg',
            'qt' => 'video/quicktime',
            'mov' => 'video/quicktime',

            // adobe
            'pdf' => 'application/pdf',
            'psd' => 'image/vnd.adobe.photoshop',
            'ai' => 'application/postscript',
            'eps' => 'application/postscript',
            'ps' => 'application/postscript',

            // ms office
            'doc' => 'application/msword',
            'rtf' => 'application/rtf',
            'xls' => 'application/vnd.ms-excel',
            'ppt' => 'application/vnd.ms-powerpoint',

            // open office
            'odt' => 'application/vnd.oasis.opendocument.text',
            'ods' => 'application/vnd.oasis.opendocument.spreadsheet',
        );

        $ext = strtolower(array_pop(explode('.', $file)));
        if (array_key_exists($ext, $mime_types)) {
            return $mime_types[$ext];
        } else {
            return 'application/octet-stream';
        }
    }

    /**
     * Determinate the Server OS
     *
     * @return string    The OS : WIN | UNIX | OTHER
     *
     */
    public static function detectOS()
    {
        $os = php_uname('s');
        if (stripos($os, 'win') !== false) return 'WIN';
        if (stripos($os, 'linux') !== false || stripos($os, 'unix') !== false) return 'UNIX';
        return 'OTHER';
    }
}

