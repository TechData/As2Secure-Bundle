<?php
/**
 * Created by PhpStorm.
 * User: westin
 * Date: 3/15/2015
 * Time: 11:35 AM
 */

namespace TechData\AS2SecureBundle\Factories;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use TechData\AS2SecureBundle\Interfaces\PartnerProvider;
use TechData\AS2SecureBundle\Models\AbstractBase;

abstract class AbstractFactory
{
    /**
     * @var PartnerProvider
     */
    private $partnerProvider;
    /**
     * @var EventDispatcherInterface
     */
    private $EventDispatcher;

    protected function buildAbstract(AbstractBase $object)
    {

    }

    /**
     * @return PartnerProvider
     */
    protected function getPartnerProvider()
    {
        return $this->partnerProvider;
    }

    /**
     * @param PartnerProvider $partnerProvider
     */
    public function setPartnerProvider(PartnerProvider $partnerProvider)
    {
        $this->partnerProvider = $partnerProvider;
    }

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