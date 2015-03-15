<?php
/**
 * Created by PhpStorm.
 * User: westin
 * Date: 3/15/2015
 * Time: 11:35 AM
 */

namespace TechData\AS2SecureBundle\Factories;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;

abstract class AbstractFactory
{
    /**
     * @var EventDispatcherInterface
     */
    private $EventDispatcher;

    /**
     * @return EventDispatcherInterface
     */
    protected function getEventDispatcher()
    {
        return $this->EventDispatcher;
    }

    /**
     * @param EventDispatcherInterface $EventDispatcher
     */
    public function setEventDispatcher($EventDispatcher)
    {
        $this->EventDispatcher = $EventDispatcher;
    }


}