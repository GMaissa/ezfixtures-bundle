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
 * Fixture Steps Collection
 *
 * Based on Kaliop eZMigrationBundle Migration Steps Collection Class
 *
 * @author Guillaume Maïssa <guillaume@maissa.fr>
 */
class FixtureStepsCollection extends AbstractCollection
{
    protected $allowedClass = 'GMaissa\eZFixturesBundle\API\Value\FixtureStep';
}
