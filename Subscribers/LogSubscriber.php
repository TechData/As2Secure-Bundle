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
use TechData\AS2SecureBundle\Events\Log;


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
            array('log' => 'log'),
        );
    }

    public function log(Log $event)
    {

    }
}