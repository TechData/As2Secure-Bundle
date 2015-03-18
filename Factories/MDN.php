<?php
/**
 * Created by PhpStorm.
 * User: westin
 * Date: 3/15/2015
 * Time: 11:30 AM
 */

namespace TechData\AS2SecureBundle\Factories;


use TechData\AS2SecureBundle\Models\MDN as MDNModel;

class MDN extends AbstractFactory
{

    function __construct()
    {

    }

    /**
     * @param null $data
     * @param array $params
     * @return MDNModel
     * @throws \TechData\AS2SecureBundle\Models\AS2Exception
     */
    public function build($data = null, $params = array())
    {
        $mdn = new MDNModel();
        $mdn->setPartnerFactory($this->getPartnerFactory());
        $mdn->setAdapterFactory($this->getAdapterFactory());
        $mdn->initialize($data, $params);
        return $mdn;

    }


}