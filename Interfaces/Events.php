<?php
/**
 * Created by PhpStorm.
 * User: westin
 * Date: 3/15/2015
 * Time: 6:16 PM
 */

namespace TechData\AS2SecureBundle\Interfaces;


/**
 * Interface Events
 *
 * @package TechData\AS2SecureBundle\Interfaces
 */
interface Events
{
    /**
     *
     */
    CONST LOG = 'tech_data_as2_secure.event.log';
    /**
     *
     */
    CONST ERROR = 'tech_data_as2_secure.event.error';
    /**
     *
     */
    CONST MESSAGE_RECIEVED = 'tech_data_as2_secure.event.message_received';
    /**
     *
     */
    CONST MESSAGE_SENT = 'tech_data_as2_secure.event.message_sent';
}