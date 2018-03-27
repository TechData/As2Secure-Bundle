<?php

namespace TechData\AS2SecureBundle\Interfaces;

/**
 *
 * @author pvesin
 */
/**
 * Interface PartnerInterface
 *
 * @package TechData\AS2SecureBundle\Interfaces
 */
interface PartnerInterface
{
    public function getData(): array;

    public function getId(): string;
}