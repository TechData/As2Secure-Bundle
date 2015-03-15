<?php
/**
 * Created by PhpStorm.
 * User: westin
 * Date: 3/15/2015
 * Time: 12:22 PM
 */

namespace TechData\AS2SecureBundle\DependencyInjection\CompilerPass;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;


class PartnerProviderCompilerPass
{
    public function process(ContainerBuilder $container)
    {
        // Get the reference for the service
        $reference = new Reference($container->getParameter('tech_data_as2_secure.partner_provider.service_id'));

        // Add to the abstract factory
        $container->getDefinition('tech_data_as2_secure.factory.abstract')->addMethodCall('setPartnerProvider', array($reference));

        // Add to the AS2 Handler
        $container->getDefinition('tech_data_as2_secure.handler.as2')->addMethodCall('setPartnerProvider', array($reference));
    }
}