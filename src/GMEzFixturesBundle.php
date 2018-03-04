<?php
/**
 * File part of the eZFixturesBundle project.
 *
 * @copyright 2018 Guillaume Maïssa
 * @license   https://opensource.org/licenses/MIT The MIT License (MIT)
 */

namespace GMaissa\eZFixturesBundle;

use GMaissa\eZFixturesBundle\DependencyInjection\CompilerPass\TaggedServicesCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * eZ publish fixtures bundle declaration class
 *
 * @author Guillaume Maïssa <guillaume@maissa.fr>
 */
class GMEzFixturesBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new TaggedServicesCompilerPass());
    }
}
