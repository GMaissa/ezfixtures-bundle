<?php
/**
 * File part of the eZFixturesBundle project.
 *
 * @copyright 2018 Guillaume Maïssa
 * @license   https://opensource.org/licenses/MIT The MIT License (MIT)
 */

namespace GMaissa\eZFixturesBundle\Core\Parser;

use GMaissa\eZFixturesBundle\API\Value\FixtureDefinition;
use GMaissa\eZFixturesBundle\API\Value\FixtureStep;

/**
 * Abstract Fixture Definition File Parser
 *
 * Based on Kaliop eZMigrationBundle Abstract Migration Definition Parser Class
 *
 * @author Guillaume Maïssa <guillaume@maissa.fr>
 */
class AbstractDefinitionParser
{
    /**
     * Parses a fixture definition in the form of an array of steps
     *
     * @param array             $data
     * @param FixtureDefinition $definition
     * @param string            $format
     *
     * @return FixtureDefinition
     */
    protected function parseDefinitionData($data, FixtureDefinition $definition, $format = 'Yaml')
    {
        $status = FixtureDefinition::STATUS_PARSED;

        if (!is_array($data)) {
            $status  = FixtureDefinition::STATUS_INVALID;
            $message = "$format fixture file '{$definition->path}' must contain an array as top element";
        } else {
            foreach ($data as $i => $stepDef) {
                if (!isset($stepDef['type']) || !is_string($stepDef['type'])) {
                    $status  = FixtureDefinition::STATUS_INVALID;
                    $message = "$format fixture file '{$definition->path}' misses or has a non-string 'type'"
                             . " element in step $i";
                    break;
                }
            }
        }

        if ($status != FixtureDefinition::STATUS_PARSED) {
            return new FixtureDefinition(
                $definition->name,
                $definition->path,
                $definition->rawDefinition,
                $status,
                array(),
                $message
            );
        }

        $stepDefs = array();
        foreach ($data as $stepDef) {
            $type = $stepDef['type'];
            unset($stepDef['type']);
            $stepDefs[] = new FixtureStep($type, $stepDef, array('path' => $definition->path));
        }

        return new FixtureDefinition(
            $definition->name,
            $definition->path,
            $definition->rawDefinition,
            FixtureDefinition::STATUS_PARSED,
            $stepDefs
        );
    }
}
