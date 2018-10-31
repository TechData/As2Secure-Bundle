<?php

namespace TechData\AS2SecureBundle\Events;

use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Description of MessageReceived
 *
 * @author wpigott
 */
class MessageReceived extends Event
{
    
    /**
     *
     */
    const EVENT = 'as2.messageReceived';
    /**
     *
     */
    const TYPE_MESSAGE = 'message';
    /**
     *
     */
    const TYPE_MDN = 'mdn';
    
    /**
     * @var string
     */
    private $message;
    
    /**
     * @var string
     */
    private $messageId;
    
    /**
     * @var
     */
    private $originalMessageId;
    /**
     * @var
     */
    private $type;
    
    /**
     * @var string
     */
    private $sendingPartnerId;
    
    /**
     * @var string
     */
    private $receivingPartnerId;
    
    /**
     * @return string
     */
    public function getSendingPartnerId()
    {
        return $this->sendingPartnerId;
    }
    
    /**
     * @return string
     */
    public function getMessageId()
    {
        return $this->messageId;
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
     *
     * @return MessageReceived
     */
    public function setType($type)
    {
        $this->type = $type;
        
        return $this;
    }
    
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
        
        return $this;
    }
    
    /**
     * @param string $messageId
     */
    public function setMessageId($messageId)
    {
        $this->messageId = $messageId;
        
        return $this;
    }
    
    /**
     * @param string $sendingPartnerId
     *
     * @return MessageReceived
     */
    public function setSendingPartnerId($sendingPartnerId)
    {
        $this->sendingPartnerId = $sendingPartnerId;
        
        return $this;
    }
    
    /**
     * @return string
     */
    public function getReceivingPartnerId()
    {
        return $this->receivingPartnerId;
    }
    
    /**
     * @param string $receivingPartnerId
     *
     * @return MessageReceived
     */
    public function setReceivingPartnerId($receivingPartnerId)
    {
        $this->receivingPartnerId = $receivingPartnerId;
        
        return $this;
    }
    
    /**
     * @param $message
     *
     * @return $this
     */
    public function setMessage($message)
    {
        $this->message = $message;
        
        return $this;
    }
    
    /**
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }
}
