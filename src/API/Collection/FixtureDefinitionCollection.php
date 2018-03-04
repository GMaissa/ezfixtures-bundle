<?php
/**
 * File part of the eZFixturesBundle project.
 *
 * @copyright 2018 Guillaume Maïssa
 * @license   https://opensource.org/licenses/MIT The MIT License (MIT)
 */

namespace GMaissa\eZFixturesBundle\API\Collection;

use Kaliop\eZMigrationBundle\API\Collection\AbstractCollection;

/**
 * Fixture Definition Collection
 *
 * Based on Kaliop eZMigrationBundle Migration Definition Collection Class
 *
 * @author Guillaume Maïssa <guillaume@maissa.fr>
 */
class FixtureDefinitionCollection extends AbstractCollection
{
    protected $allowedClass = 'GMaissa\eZFixturesBundle\API\Value\FixtureDefinition';
}
