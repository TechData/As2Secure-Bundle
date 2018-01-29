<?php

namespace TechData\AS2SecureBundle\Events;

use Symfony\Component\EventDispatcher\Event;

/**
 * Description of Log
 *
 * @author wpigott
 */
class Log extends Event
{
    
    /**
     *
     */
    const EVENT = 'as2.log';
    /**
     *
     */
    const TYPE_INFO = 'INFO';
    /**
     *
     */
    const TYPE_WARN = 'WARN';
    /**
     *
     */
    const TYPE_ERROR = 'ERROR';
    
    /**
     * @var
     */
    private $message;
    /**
     * @var
     */
    private $type;
    
    /**
     * Log constructor.
     *
     * @param $type
     * @param $message
     */
    function __construct($type, $message)
    {
        $this->type    = $type;
        $this->message = $message;
    }
    
    
    /**
     * @return mixed
     */
    public function getMessage()
    {
        return $this->message;
    }
    
    /**
     * @param $message
     */
    public function setMessage($message)
    {
        $this->message = $message;
    }
    
    /**
     * @return mixed
     */
    public function getType()
    {
        return $this->type;
    }
    
    /**
     * @param mixed $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }
    
    
}
