<?php
/**
 * Created by PhpStorm.
 * User: westin
 * Date: 3/15/2015
 * Time: 11:35 AM
 */

namespace TechData\AS2SecureBundle\Factories;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use TechData\AS2SecureBundle\Factories\Partner as PartnerFactory;
use TechData\AS2SecureBundle\Factories\Adapter as AdapterFactory;

/**
 * Class AbstractFactory
 *
 * @package TechData\AS2SecureBundle\Factories
 */
abstract class AbstractFactory
{
    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;
    
    /**
     * @var PartnerFactory
     */
    private $partnerFactory;
    
    /**
     * @var AdapterFactory
     */
    private $adapterFactory;
    
    /**
     * @return EventDispatcherInterface
     */
    protected function getEventDispatcher()
    {
        return $this->eventDispatcher;
    }
    
    /**
     * @param EventDispatcherInterface $EventDispatcher
     */
    public function setEventDispatcher(EventDispatcherInterface $EventDispatcher)
    {
        $this->eventDispatcher = $EventDispatcher;
    }
    
    /**
     *
     * @return PartnerFactory
     */
    public function getPartnerFactory()
    {
        return $this->partnerFactory;
    }
    
    /**
     * @param Partner $partnerFactory
     */
    public function setPartnerFactory(PartnerFactory $partnerFactory)
    {
        $this->partnerFactory = $partnerFactory;
    }
    
    /**
     * @return AdapterFactory
     */
    public function getAdapterFactory()
    {
        return $this->adapterFactory;
    }
    
    /**
     * @param Adapter $adapterFactory
     */
    public function setAdapterFactory(AdapterFactory $adapterFactory)
    {
        $this->adapterFactory = $adapterFactory;
    }
    
    
}