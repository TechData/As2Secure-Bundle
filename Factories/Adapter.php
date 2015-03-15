<?php
/**
 * Created by PhpStorm.
 * User: westin
 * Date: 3/15/2015
 * Time: 5:39 PM
 */

namespace TechData\AS2SecureBundle\Factories;

use TechData\AS2SecureBundle\Factories\Partner as PartnerFactory;
use TechData\AS2SecureBundle\Models\Adapter as AdapterModel;

class Adapter
{
    /**
     * @var PartnerFactory
     */
    private $partnerFactory;

    /**
     * @param $partner_from
     * @param $partner_to
     * @return AdapterModel
     * @throws \TechData\AS2SecureBundle\Models\AS2Exception
     */
    public function build($partner_from, $partner_to)
    {
        $adapter = new AdapterModel($this->partnerFactory);
        $adapter->initialize($partner_from, $partner_to);
        return $adapter;
    }
}