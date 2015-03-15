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

class AS2Log
{
    public static $filename = 'events.log';

    protected static $stack = array();

    const INFO = 'info';
    const ERROR = 'error';
    const WARNING = 'warning';
    const FAILURE = 'failure';

    // the last/current message_id
    protected static $current_message_id = '';

    protected $message_id = '';
    protected $message = '';
    protected $code = 0;
    protected $level = self::INFO;

    /**
     * Contructor
     *
     * @comment Only available from static methods (info | warning | error)
     */
    protected function __construct($message, $code = 0, $level = self::INFO)
    {
        $this->message_id = self::$current_message_id;
        $this->message = $message;
        $this->code = $code;
        $this->level = $level;

        self::logEvent($this);
    }

    /**
     * Log event into a file
     *
     * @param object $event The event to log into file
     */
    protected function logEvent($event, $log_message_id = true)
    {
        umask(000);
        if (!file_exists(AS2_DIR_LOGS))
            mkdir(AS2_DIR_LOGS, 0777, true);

        if ($log_message_id)
            $message = '[' . date('Y-m-d H:i:s') . '] ' . trim($event->message_id, '<>') . ' : (' . strtoupper($event->level) . ') ' . $event->message . "\n";
        else
            $message = '[' . date('Y-m-d H:i:s') . '] (' . strtoupper($event->level) . ') ' . $event->message . "\n";

        file_put_contents(AS2_DIR_LOGS . self::$filename, $message, FILE_APPEND);
    }

    /**
     * Return the message id (from mime part)
     *
     * @return string
     */
    public function getMessageId()
    {
        return self::$current_message_id;
    }

    /**
     * Return the message
     *
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * Return the code
     *
     * @return int
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Return the level
     *
     * @return string
     */
    public function getLevel()
    {
        return $this->level;
    }

    /**
     * Static handler for info level
     *
     * @param string $message_id The current message id (from mime message)
     * @param string $message The message to log
     * @param int $code The code number
     */
    public static function info($message_id, $message, $code = 0)
    {
        self::handleLevel(self::INFO, $message_id, $message, $code);
    }

    /**
     * Static handler for warning level
     *
     * @param string $message_id The current message id (from mime message)
     * @param string $message The message to log
     * @param int $code The code number
     */
    public static function warning($message_id, $message, $code = 0)
    {
        self::handleLevel(self::WARNING, $message_id, $message, $code);
    }

    /**
     * Static handler for error level
     *
     * @param string $message_id The current message id (from mime message)
     * @param string $message The message to log
     * @param int $code The code number
     */
    public static function error($message_id, $message, $code = 0)
    {
        self::handleLevel(self::ERROR, $message_id, $message, $code);
    }

    /**
     * Static and generic handler
     *
     * @param string $level The level
     * @param string $message_id The current message id (from mime message)
     * @param string $message The message to log
     * @param int $code The code number
     */
    public static function handleLevel($level, $message_id, $message, $code = 0)
    {
        // if $message_id is false or empty, allow to reuse the last one
        if ($message_id) self::$current_message_id = $message_id;
        $error = new self($message, $code, $level);
        self::$stack[] = $error;
    }

    /**
     * Return the stack of log for a specific level (or not)
     *
     * @param string $level The level to search for
     *
     * @return array          The current stack of logs
     */
    public static function getStack($level = null)
    {
        if ($level) {
            $tmp = array();
            foreach (self::$stack as $event)
                if ($event->level == $event)
                    $tmp[] = $event;
            return $tmp;
        }
        return self::$stack;
    }

    /**
     * Return the stack of log for a specific level (or not)
     *
     * @param string $level The level to search for
     *
     * @return array          The current stack of logs
     */
    public static function getCount($level = null)
    {
        return count(self::getStack($level));
    }

    /**
     * Indicate if there is errors into the stack
     *
     * @return boolean
     */
    public static function hasError()
    {
        foreach (self::$stack as $event)
            if ($event->level == self::ERROR)
                return true;
        return false;
    }

    /**
     * Return the lasts logs from logfile (work only on unix systems)
     *
     * @param int $count The count of log lines
     * @param boolean $reverse To reverse order of log lines
     *
     * @return string
     */
    public static function getLastLogEvents($count = 40, $reverse = true)
    {
        // build command line
        $command = 'cat -n ' . escapeshellarg(AS2_DIR_LOGS . self::$filename);
        if ($reverse) $command .= ' | tail -n ' . escapeshellarg((int)$count) . ' | sort -r | cut -f2-20';

        // exec command line
        $logs = AS2Adapter::exec($command, true);

        // return logs
        return $logs;
    }
}
