<?php

namespace TechData\AS2SecureBundle\Providers;


use TechData\AS2SecureBundle\Interfaces\PartnerInterface;
use TechData\AS2SecureBundle\Interfaces\PartnerProvider;

/**
 * Class PartnerProviderResolver
 * @package TechData\AS2SecureBundle\Providers
 */
class PartnerProviderResolver implements PartnerProvider
{
    /**
     * @var ArrayCollection
     */
    protected $mapping;

    /**
     * StatisticsChainer constructor.
     */
    public function __construct()
    {
        $this->mapping = [];
    }

    /**
     * @param PartnerInterface $partner
     */
    protected function getId(PartnerInterface $partner) {
        $data = $partner->getData();
        if(empty($data['id'])) {
            throw new \InvalidArgumentException('id is not found in your data partner');
        }

        return $data['id'];
    }

    /**
     * @param Partner $partner
     */
    public function add(PartnerInterface $partner)
    {
        $partnerId  = $this->getId($partner);
        $this->mapping[$partnerId] = $partner;
    }

    /**
     * @param null $context
     *
     * @return mixed
     */
    public function getPartner($partnerId, $reload = false)
    {
        return $this->mapping[$partnerId]->getData();
    }
}