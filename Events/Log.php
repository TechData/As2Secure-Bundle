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

    const EVENT = 'LOG';
    const TYPE_INFO = 'INFO';
    const TYPE_WARN = 'WARN';
    const TYPE_ERROR = 'ERROR';

    private $message;
    private $type;

    function __construct($type, $message)
    {
        $this->type = $type;
        $this->message = $message;
    }


    public function getMessage()
    {
        return $this->message;
    }

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
