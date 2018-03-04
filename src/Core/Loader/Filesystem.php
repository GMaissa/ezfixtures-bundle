<?php
/**
 * File part of the eZFixturesBundle project.
 *
 * @copyright 2018 Guillaume Maïssa
 * @license   https://opensource.org/licenses/MIT The MIT License (MIT)
 */

namespace GMaissa\eZFixturesBundle\Core\Loader;

use GMaissa\eZFixturesBundle\API\Collection\FixtureDefinitionCollection;
use GMaissa\eZFixturesBundle\API\FixturesLoaderInterface;
use GMaissa\eZFixturesBundle\API\Value\FixtureDefinition;

/**
 * Filesystem Fixture Definition Loader Class
 *
 * Based on Kaliop eZMigrationBundle Filesystem Migration Definition Loader Class
 *
 * @author Guillaume Maïssa <guillaume@maissa.fr>
 */
class Filesystem implements FixturesLoaderInterface
{
    /**
     * @param array $paths either dir names or file names.
     *
     * @return FixtureDefinition[] migrations definitions. key: name, value: file path
     * @throws \Exception
     */
    public function listAvailableDefinitions(array $paths = array())
    {
        return $this->getDefinitions($paths, true);
    }

    /**
     * @param array $paths either dir names or file names
     *                     If empty will load all available fixtures in fixtures base directory
     *
     * @return FixtureDefinitionCollection definitions. key: name, value: contents of the definition as string
     * @throws \Exception
     */
    public function loadDefinitions(array $paths = array())
    {
        return new FixtureDefinitionCollection($this->getDefinitions($paths, false));
    }

    /**
     * @param array $paths          either dir names or file names
     * @param bool  $returnFilename return either the
     *
     * @return FixtureDefinition[]|string[] fixtures definitions. key: name, value: contents of the definition, as
     *                                      string or file path
     * @throws \Exception
     */
    protected function getDefinitions(array $paths = array(), $returnFilename = false)
    {
        $definitions = array();
        foreach ($paths as $path) {
            if (is_file($path)) {
                $definitions[basename($path)] = $returnFilename ? $path : new FixtureDefinition(
                    basename($path),
                    $path,
                    file_get_contents($path)
                );
            } elseif (is_dir($path)) {
                foreach (new \DirectoryIterator($path) as $file) {
                    if ($file->isFile()) {
                        $definitions[$file->getFilename()] =
                            $returnFilename ? $file->getRealPath() : new FixtureDefinition(
                                $file->getFilename(),
                                $file->getRealPath(),
                                file_get_contents($file->getRealPath())
                            );
                    }
                }
            } else {
                throw new \Exception("Path '$path' is neither a file nor directory");
            }
        }
        ksort($definitions);

        return $definitions;
    }
}
