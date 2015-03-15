<?php

namespace TechData\AS2SecureBundle\Events;

use Symfony\Component\EventDispatcher\Event;

/**
 * Description of MessageSent
 *
 * @author wpigott
 */
class MessageSent extends Event {
    const EVENT = 'MESSAGE_SENT';

    private $message;
    private $messageType;
    private $headers = array();

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
