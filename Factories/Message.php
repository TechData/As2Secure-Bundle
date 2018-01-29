<?php
/**
 * Created by PhpStorm.
 * User: westin
 * Date: 3/15/2015
 * Time: 11:30 AM
 */

namespace TechData\AS2SecureBundle\Factories;

use TechData\AS2SecureBundle\Factories\AbstractFactory;
use TechData\AS2SecureBundle\Factories\MDN as MDNFactory;
use TechData\AS2SecureBundle\Models\Message as MessageModel;

/**
 * Class Message
 *
 * @package TechData\AS2SecureBundle\Factories
 */
class Message extends AbstractFactory
{
    /**
     * @var MDNFactory
     */
    private $mdnFactory;
    
    /**
     * Message constructor.
     *
     * @param MDN $mdnFactory
     */
    function __construct(MDNFactory $mdnFactory)
    {
        $this->mdnFactory = $mdnFactory;
    }
    
    /**
     * @param null  $data
     * @param array $params
     *
     * @return MessageModel
     */
    public function build($data = null, $params = [])
    {
        $message = new MessageModel($this->mdnFactory);
        $message->setPartnerFactory($this->getPartnerFactory());
        $message->setAdapterFactory($this->getAdapterFactory());
        $message->initialize($data, $params);
        
        return $message;
        
    }
}