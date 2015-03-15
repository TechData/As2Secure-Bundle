<?php

namespace TechData\AS2SecureBundle\Factories;

use TechData\AS2SecureBundle\Interfaces\PartnerProvider;
use TechData\AS2SecureBundle\Models\Partner as PartnerModel;

/**
 * Description of Partner
 *
 * @author wpigott
 */
class Partner
{

    private $partnerProvider;
    private $loadedPartners = array();

    public function __construct(PartnerProvider $partnerProvider)
    {
        $this->partnerProvider = $partnerProvider;
    }

    /**
     * @param $partnerId
     * @param bool $reload
     * @return PartnerModel
     */
    public function getPartner($partnerId, $reload=FALSE)
    {
        if($reload || !array_key_exists(trim($partnerId))) {
            $partnerData = $this->partnerProvider->getPartner($partnerId);
            $as2partner = new AS2Partner((array)$partnerData);
            $this->loadedPartners[trim($partnerId)] = $as2partner;
        }
        return $this->loadedPartners[trim($partnerId)];
    }

    /**
     * @param array $partnerData
     * @return PartnerModel
     */
    private function makeNewPartner($partnerData) {
        return new PartnerModel((array)$partnerData);
    }

}
