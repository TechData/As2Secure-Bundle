<?php
/**
 * Created by PhpStorm.
 * User: westin
 * Date: 3/15/2015
 * Time: 12:04 PM
 */

namespace TechData\AS2SecureBundle\Subscribers;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use TechData\AS2SecureBundle\Events\Error;
use TechData\AS2SecureBundle\Events\Log;
use TechData\AS2SecureBundle\Events\MessageReceived;
use TechData\AS2SecureBundle\Events\MessageSent;
use TechData\AS2SecureBundle\Interfaces\Events;


class LogSubscriber implements EventSubscriberInterface
{


    /**
     *
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;


    public function __construct(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    public static function getSubscribedEvents()
    {
        return array(
            Log::EVENT => array('log'),
            Error::EVENT => array('error'),
            MessageReceived::EVENT => array('messageReceived'),
            MessageSent::EVENT => array('messageSent'),
        );
    }

    public function log(Log $event)
    {
        $this->eventDispatcher->dispatch(Events::LOG, $event);
    }

    public function error(Error $event)
    {
        $this->eventDispatcher->dispatch(Events::ERROR, $event);
    }

    public function messageReceived(MessageReceived $event)
    {
        $this->eventDispatcher->dispatch(Events::MESSAGE_RECIEVED, $event);
    }

    public function messageSent(MessageSent $event)
    {
        $this->eventDispatcher->dispatch(Events::MESSAGE_SENT, $event);
    }


}