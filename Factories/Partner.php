<?php

namespace TechData\AS2SecureBundle\Factories;

use TechData\AS2SecureBundle\Interfaces\PartnerProvider;

/**
 * Description of Partner
 *
 * @author wpigott
 */
class Partner {

    private $partnerProvider;

    public function __construct(PartnerProvider $partnerProvider) {
        $this->partnerProvider = $partnerProvider;
    }
    
    public function getPartner($partnerId) {
        $partner = $this->partnerProvider->getPartner($partnerId);
        $as2partner = new AS2Partner((array)$partner);
        }

}
