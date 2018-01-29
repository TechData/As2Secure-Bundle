<?php

namespace TechData\AS2SecureBundle\Events;

use Symfony\Component\EventDispatcher\Event;

/**
 * Description of Error
 *
 * @author wpigott
 */
class Error extends Event
{
    const EVENT = 'as2.error';
}
