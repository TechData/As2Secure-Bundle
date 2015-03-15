<?php
/**
 * Created by PhpStorm.
 * User: westin
 * Date: 3/15/2015
 * Time: 11:30 AM
 */

namespace TechData\AS2SecureBundle\Factories;

use TechData\AS2SecureBundle\Factories\MDN as MDNFactory;
use TechData\AS2SecureBundle\Models\Message as MessageModel;

class Message
{
    /**
     * @var MDNFactory
     */
    private $mdnFactory;

    function __construct(MDNFactory $mdnFactory)
    {
        $this->mdnFactory = $mdnFactory;
    }

    /**
     * @param null $data
     * @param array $params
     * @return MessageModel
     */
    public function build($data = null, $params = array())
    {
        $message = new MessageModel($this->mdnFactory);
        $message->initialize($data, $params);
        return $message;

    }
}