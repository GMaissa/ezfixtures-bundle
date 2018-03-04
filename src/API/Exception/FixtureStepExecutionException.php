<?php
/**
 * File part of the eZFixturesBundle project.
 *
 * @copyright 2018 Guillaume Maïssa
 * @license   https://opensource.org/licenses/MIT The MIT License (MIT)
 */

namespace GMaissa\eZFixturesBundle\API\Exception;

use Kaliop\eZMigrationBundle\API\Exception\MigrationStepExecutionException;

/**
 * Fixture Step Execution Exception
 *
 * Based on Kaliop eZMigrationBundle Migration Step Execution Exception
 *
 * @author Guillaume Maïssa <guillaume@maissa.fr>
 */
class FixtureStepExecutionException extends MigrationStepExecutionException
{
}
