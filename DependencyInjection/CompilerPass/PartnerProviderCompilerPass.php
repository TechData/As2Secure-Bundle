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
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;


/**
 * Class PartnerProviderCompilerPass
 *
 * @package TechData\AS2SecureBundle\DependencyInjection\CompilerPass
 */
class PartnerProviderCompilerPass implements CompilerPassInterface
{
    /**
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        // Get the reference for the service
        $reference = new Reference($container->getParameter('tech_data_as2_secure.partner_provider.service_id'));
        
        // Add to the partner factory
        $container->getDefinition('tech_data_as2_secure.factory.partner')->setArguments([$reference]);
    }
}