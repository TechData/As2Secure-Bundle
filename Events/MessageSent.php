<?php

namespace TechData\AS2SecureBundle\Events;

use Symfony\Component\EventDispatcher\Event;

/**
 * Description of MessageSent
 *
 * @author wpigott
 */
class MessageSent extends Event {
    const EVENT = 'as2.messageSent';
    const TYPE_MESSAGE = 'message';
    const TYPE_MDN = 'mdn';

    private $message;
    private $messageId;
    private $messageType;
    private $type;
    private $code;
    private $originalMessageId;
    private $headers = array();
    
    /**
     * @return mixed
     */
    public function getOriginalMessageId()
    {
        return $this->originalMessageId;
    }
    
    /**
     * @param mixed $originalMessageId
     */
    public function setOriginalMessageId($originalMessageId)
    {
        $this->originalMessageId = $originalMessageId;
    }
    
    
    /**
     * @return mixed
     */
    public function getCode()
    {
        return $this->code;
    }
    
    /**
     * @param mixed $code
     */
    public function setCode($code)
    {
        $this->code = $code;
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
    
    /**
     * @return mixed
     */
    public function getMessageId()
    {
        return $this->messageId;
    }
    
    /**
     * @param mixed $messageId
     */
    public function setMessageId($messageId)
    {
        $this->messageId = $messageId;
    }

    
    public function getMessage() {
        return $this->message;
    }

    public function setMessage($message) {
        $this->message = $message;
    }

    public function getMessageType() {
        return $this->messageType;
    }

    public function getHeaders() {
        return $this->headers;
    }

    public function setMessageType($messageType) {
        $this->messageType = $messageType;
    }

    public function setHeaders($headers) {
        $this->headers = $headers;
    }
}
