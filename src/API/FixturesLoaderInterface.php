<?php
/**
 * File part of the eZFixturesBundle project.
 *
 * @copyright 2018 Guillaume Maïssa
 * @license   https://opensource.org/licenses/MIT The MIT License (MIT)
 */

namespace GMaissa\eZFixturesBundle\API;

use GMaissa\eZFixturesBundle\API\Collection\FixtureDefinitionCollection;

/**
 * Fixtures Loader Interface
 *
 * Based on Kaliop eZMigrationBundle Migration Loader Interface
 *
 * @author Guillaume Maïssa <guillaume@maissa.fr>
 */
interface FixturesLoaderInterface
{
    /**
     * @param array $paths either dir names or file names
     *
     * @return string[] fixtures definitions. key: name, value: path
     * @throws \Exception
     */
    public function listAvailableDefinitions(array $paths = array());

    /**
     * @param array $paths
     *
     * @return FixtureDefinitionCollection unparsed definitions. key has to be the fixture name
     * @throws \Exception
     */
    public function loadDefinitions(array $paths = array());
}
