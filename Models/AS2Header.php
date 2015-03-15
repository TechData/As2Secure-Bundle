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

class AS2Header implements Countable, ArrayAccess, Iterator
{
    protected $headers = array();

    protected $_position = null;

    public function __construct($data = null)
    {
        if (is_array($data)) {
            $this->headers = $data;
        } elseif ($data instanceof AS2Header) {
            $this->headers = $data->getHeaders();
        }
    }

    /**
     * Reset all current headers with new values
     *
     * @param array $headers The new headers to use
     */
    public function setHeaders($headers)
    {
        $this->headers = array();
        $this->addHeaders($headers);
    }

    /**
     * Add new header (or override current one)
     *
     * @param string $key The name of the header
     * @param string $value The value of the header
     */
    public function addHeader($key, $value)
    {
        $this->headers[$key] = "$value";
    }

    /**
     * Add a set of headers (or override currents)
     *
     * @param array $headers The new headers to use
     */
    public function addHeaders($values)
    {
        foreach ($values as $key => $value)
            $this->addHeader($key, $value);
    }

    /**
     * Add a set of headers extracted from a mime message
     *
     * @param string $message The message content to use
     */
    public function addHeadersFromMessage($message)
    {
        $headers = self::parseText($message);
        if (count($headers)) {
            foreach ($headers->getHeaders() as $key => $value)
                $this->addHeader($key, $value);
        }
    }

    /**
     * Remove an header
     *
     * @param string $key The name of the header
     */
    public function removeHeader($key)
    {
        unset($this->headers[$key]);
    }

    /**
     * Return all headers as an array
     *
     * @return array   The headers, eg: array(name1 => value1, name2 => value2)
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * Return all headers as a formatted array
     *
     * @return array   The headers, eg: array(0 => name1:value1, 1 => name2:value2)
     */
    public function toFormattedArray()
    {
        $tmp = array();
        foreach ($this->headers as $key => $val) {
            $tmp[] = $key . ': ' . $val;
        }
        return $tmp;
    }

    /**
     * Return the value of an header
     *
     * @param string $key The header
     *
     * @return string        The value corresponding
     */
    public function getHeader($key)
    {
        $key = strtolower($key);
        $tmp = array_change_key_case($this->headers);
        if (isset($tmp[$key])) return $tmp[$key];
        return false;
    }

    /**
     * Return the count of headers
     *
     * @return int
     */
    public function count()
    {
        return count($this->headers);
    }

    /**
     * Check if an header exists
     *
     * @param string $key The header to check existance
     *
     * @return boolean
     */
    public function exists($key)
    {
        $tmp = array_change_key_case($this->headers);
        return array_key_exists(strtolower($key), $tmp);
    }

    /**
     * Magic method that returns headers serialized as in mime message
     *
     * @return string
     */
    public function __toString()
    {
        $ret = '';

        foreach ($this->headers as $key => $value) {
            $ret .= $key . ': ' . $value . "\n";
        }

        return rtrim($ret);
    }

    /***************************/
    /** ArrayAccess interface **/
    /***************************/

    public function offsetExists($offset)
    {
        return array_key_exists($this->headers, $offset);
    }

    public function offsetGet($offset)
    {
        return $this->headers[$offset];
    }

    public function offsetSet($offset, $value)
    {
        $this->headers[$offset] = $value;
    }

    public function offsetUnset($offset)
    {
        unset($this->headers[$offset]);
    }

    /************************/
    /** Iterator interface **/
    /************************/

    public function current()
    {
        return $this->headers[$this->key()];
    }

    public function key()
    {
        $keys = array_keys($this->headers);
        return $keys[$this->_position];
    }

    public function next()
    {
        $this->_position++;
    }

    public function rewind()
    {
        $this->_position = 0;
    }

    public function valid()
    {
        return ($this->_position >= 0 && $this->_position < count($this->headers));
    }

    /**
     * Extract headers from mime message and return a new instance of AS2Header
     *
     * @param string  The content to parse
     *
     * @return object  AS2Header instance
     */
    public static function parseText($text)
    {
        if (strpos($text, "\n\n") !== false) $text = substr($text, 0, strpos($text, "\n\n"));
        $text = rtrim($text) . "\n";

        $matches = array();
        preg_match_all('/(.*?):\s*(.*?\n(\s.*?\n)*)/', $text, $matches);
        if ($matches) {
            foreach ($matches[2] as &$value) $value = trim(str_replace(array("\r", "\n"), ' ', $value));
            unset($value);
            if (count($matches[1]) && count($matches[1]) == count($matches[2])) {
                $headers = array_combine($matches[1], $matches[2]);
                return new self($headers);
            }
        }

        return new self();
    }

    /**
     * Extract headers from http request and return a new instance of AS2Header
     *
     * @param string  The content to parse
     *
     * @return object  AS2Header instance
     */
    public static function parseHttpRequest()
    {
        /**
         * Fix to get request headers from Apache even on PHP running as a CGI
         */
        if (!function_exists('apache_request_headers')) {
            $headers = array('Content-Type' => $_SERVER['CONTENT_TYPE'],
                'Content-Length' => $_SERVER['CONTENT_LENGTH']);

            foreach ($_SERVER as $key => $value) {
                if (strpos($key, 'HTTP_') === 0) {
                    // 5 is to remove 'HTTP_'
                    $key = str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($key, 5)))));
                    $headers[$key] = $value;
                }
            }

            return new self($headers);
        } else {
            return new self(apache_request_headers());
        }
    }
}
