<?php
/**
 * Created by PhpStorm.
 * User: westin
 * Date: 3/15/2015
 * Time: 8:26 PM
 */

namespace TechData\AS2SecureBundle\Interfaces;


/**
 * Interface MessageSender
 *
 * @package TechData\AS2SecureBundle\Interfaces
 */
interface MessageSender
{
    /**
     * @param $toPartner
     * @param $fromPartner
     * @param $messageContent
     *
     * @throws \Exception
     * @throws \TechData\AS2SecureBundle\Models\AS2Exception
     * @throws \TechData\AS2SecureBundle\Models\Exception
     */
    public function sendMessage($toPartner, $fromPartner, $messageContent);
}