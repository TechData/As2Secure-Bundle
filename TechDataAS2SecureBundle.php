<?php

namespace TechData\AS2SecureBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use TechData\AS2SecureBundle\DependencyInjection\CompilerPass\PartnerProviderCompilerPass;

class TechDataAS2SecureBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new PartnerProviderCompilerPass());
    }
}
