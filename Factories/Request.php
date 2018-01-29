<?php
/**
 * Created by PhpStorm.
 * User: westin
 * Date: 3/15/2015
 * Time: 11:30 AM
 */

namespace TechData\AS2SecureBundle\Factories;

use Symfony\Component\EventDispatcher\EventDispatcher;
use TechData\AS2SecureBundle\Factories\AbstractFactory;
use TechData\AS2SecureBundle\Factories\MDN as MDNFactory;
use TechData\AS2SecureBundle\Factories\Message as MessageFactory;
use TechData\AS2SecureBundle\Models\Request as RequestModel;

/**
 * Class Request
 *
 * @package TechData\AS2SecureBundle\Factories
 */
class Request extends AbstractFactory
{
    /**
     * @var null
     */
    protected $request = null;
    /**
     * @var MDNFactory
     */
    private $mdnFactory;
    /**
     * @var MessageFactory
     */
    private $messageFactory;
    /**
     * @var EventDispatcher
     */
    private $eventDispatcher;
    
    /**
     * Request constructor.
     *
     * @param MDN             $mdnFactory
     * @param Message         $messageFactory
     * @param EventDispatcher $eventDispatcher
     */
    function __construct(MDNFactory $mdnFactory, MessageFactory $messageFactory, EventDispatcher $eventDispatcher)
    {
        $this->mdnFactory      = $mdnFactory;
        $this->messageFactory  = $messageFactory;
        $this->eventDispatcher = $eventDispatcher;
    }
    
    /**
     * @param $content
     * @param $headers
     *
     * @return RequestModel
     */
    public function build($content, $headers)
    {
        $request = new RequestModel($this->mdnFactory, $this->messageFactory, $this->eventDispatcher);
        $request->setPartnerFactory($this->getPartnerFactory());
        $request->setAdapterFactory($this->getAdapterFactory());
        $request->initialize($content, $headers);
        
        return $request;
        
    }
}