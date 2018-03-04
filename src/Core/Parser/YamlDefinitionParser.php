<?php
/**
 * File part of the eZFixturesBundle project.
 *
 * @copyright 2018 Guillaume Maïssa
 * @license   https://opensource.org/licenses/MIT The MIT License (MIT)
 */

namespace GMaissa\eZFixturesBundle\Core\Parser;

use GMaissa\eZFixturesBundle\API\Value\FixtureDefinition;
use GMaissa\eZFixturesBundle\API\DefinitionParserInterface;
use GMaissa\eZFixturesBundle\API\ParserInterface;
use Symfony\Component\Yaml\Yaml;

/**
 * Handles Yaml fixture definitions
 *
 * Based on Kaliop eZMigrationBundle Yaml Migration Definition Parser Class.
 *
 * @author Guillaume Maïssa <guillaume@maissa.fr>
 */
class YamlDefinitionParser extends AbstractDefinitionParser implements DefinitionParserInterface
{
    /**
     * Tells whether the given file can be handled by this handler, by checking e.g. the suffix
     *
     * @param string $definitionName typically a filename
     * @return bool
     */
    public function supports($definitionName)
    {
        $ext = pathinfo($definitionName, PATHINFO_EXTENSION);
        return  $ext == 'yml' || $ext == 'yaml';
    }

    /**
     * Parses a migration definition file, and returns the list of actions to take
     *
     * @param FixtureDefinition $definition
     * @return FixtureDefinition
     */
    public function parseDefinition(FixtureDefinition $definition)
    {
        try {
            $data = Yaml::parse($definition->rawDefinition);
        } catch (\Exception $e) {
            return new FixtureDefinition(
                $definition->name,
                $definition->path,
                $definition->rawDefinition,
                FixtureDefinition::STATUS_INVALID,
                array(),
                $e->getMessage()
            );
        }

        return $this->parseDefinitionData($data, $definition);
    }
}
