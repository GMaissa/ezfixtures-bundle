<?php
/**
 * File part of the eZFixturesBundle project.
 *
 * @copyright 2018 Guillaume Maïssa
 * @license   https://opensource.org/licenses/MIT The MIT License (MIT)
 */

namespace GMaissa\eZFixturesBundle\DependencyInjection\CompilerPass;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Compiler Pass Class to configuration Fixtures Service
 *
 * @author Guillaume Maïssa <guillaume@maissa.fr>
 */
class TaggedServicesCompilerPass implements CompilerPassInterface
{
    /**
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        if ($container->has('gm.ez_fixtures_bundle.fixtures_service')) {
            $fixturesService = $container->findDefinition('gm.ez_fixtures_bundle.fixtures_service');

            $definitionParsers = $container->findTaggedServiceIds('gm.ez_fixtures_bundle.definition_parser');
            foreach ($definitionParsers as $id => $tags) {
                $fixturesService->addMethodCall(
                    'addDefinitionParser',
                    array(new Reference($id))
                );
            }

            $executors = $container->findTaggedServiceIds('ez_migration_bundle.executor');
            foreach ($executors as $id => $tags) {
                $fixturesService->addMethodCall(
                    'addExecutor',
                    array(new Reference($id))
                );
            }
        }
    }
}
