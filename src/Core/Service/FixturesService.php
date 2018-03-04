<?php
/**
 * File part of the eZFixturesBundle project.
 *
 * @copyright 2018 Guillaume Maïssa
 * @license   https://opensource.org/licenses/MIT The MIT License (MIT)
 */

namespace GMaissa\eZFixturesBundle\Core\Service;

use eZ\Publish\API\Repository\Repository;
use GMaissa\eZFixturesBundle\API\Value\FixtureDefinition;
use GMaissa\eZFixturesBundle\API\Collection\FixtureDefinitionCollection;
use GMaissa\eZFixturesBundle\API\DefinitionParserInterface;
use GMaissa\eZFixturesBundle\API\Exception\FixtureStepExecutionException;
use GMaissa\eZFixturesBundle\API\FixturesLoaderInterface;
use Kaliop\eZMigrationBundle\API\ExecutorInterface;
use Kaliop\eZMigrationBundle\Core\RepositoryUserSetterTrait;

/**
 * Fixtures Service Class
 *
 * Based on Kaliop eZMigrationBundle MigrationService Class
 *
 * @author Guillaume Maïssa <guillaume@maissa.fr>
 */
class FixturesService
{
    use RepositoryUserSetterTrait;

    /**
     * The default Admin user Id, used when no Admin user is specified
     */
    const ADMIN_USER_ID = 14;

    /**
     * @var FixturesLoaderInterface $loader
     */
    protected $loader;

    /**
     * @var DefinitionParserInterface[] $definitionParsers
     */
    protected $definitionParsers = array();

    /**
     * @var ExecutorInterface[] $executors
     */
    protected $executors = array();

    /**
     * @var Repository $repository
     */
    protected $repository;

    public function __construct(FixturesLoaderInterface $loader, Repository $repository)
    {
        $this->loader = $loader;
        $this->repository = $repository;
    }

    public function addDefinitionParser(DefinitionParserInterface $definitionParser)
    {
        $this->definitionParsers[] = $definitionParser;
    }

    public function addExecutor(ExecutorInterface $executor)
    {
        foreach ($executor->supportedTypes() as $type) {
            $this->executors[$type] = $executor;
        }
    }

    /**
     * @param string $type
     *
     * @return ExecutorInterface
     * @throws \InvalidArgumentException If executor doesn't exist
     */
    public function getExecutor($type)
    {
        if (!isset($this->executors[$type])) {
            throw new \InvalidArgumentException("Executor with type '$type' doesn't exist");
        }

        return $this->executors[$type];
    }

    /**
     * @return string[]
     */
    public function listExecutors()
    {
        return array_keys($this->executors);
    }

    public function setLoader(FixturesLoaderInterface $loader)
    {
        $this->loader = $loader;
    }

    public function setRepository(Repository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @param string[] $paths
     *
     * @return FixtureDefinitionCollection key: fixture name, value: fixture definition as binary string
     */
    public function getFixturesDefinitions(array $paths = array())
    {
        $handledDefinitions = array();
        foreach ($this->loader->listAvailableDefinitions($paths) as $migrationName => $definitionPath) {
            foreach ($this->definitionParsers as $definitionParser) {
                if ($definitionParser->supports($migrationName)) {
                    $handledDefinitions[] = $definitionPath;
                }
            }
        }

        // we can not call loadDefinitions with an empty array using the Filesystem loader
        if (empty($handledDefinitions) && !empty($paths)) {
            return new FixtureDefinitionCollection();
        }

        return $this->loader->loadDefinitions($handledDefinitions);
    }

    /**
     * @param string[] $paths
     *
     * @return FixtureDefinitionCollection key: fixture name, value: fixture definition as binary string
     */
    public function getFixturesDefinition(array $paths = array())
    {
        $handledDefinitions = array();
        foreach ($this->loader->listAvailableDefinitions($paths) as $migrationName => $definitionPath) {
            foreach ($this->definitionParsers as $definitionParser) {
                if ($definitionParser->supports($migrationName)) {
                    $handledDefinitions[] = $definitionPath;
                }
            }
        }

        // we can not call loadDefinitions with an empty array using the Filesystem loader
        if (empty($handledDefinitions) && !empty($paths)) {
            return new FixtureDefinitionCollection();
        }

        return $this->loader->loadDefinitions($handledDefinitions);
    }

    /**
     *
     * Parses a fixture definition, return a parsed definition.
     * If there is a parsing error, the definition status will be updated accordingly
     *
     * @param FixtureDefinition $definition
     *
     * @return FixtureDefinition
     * @throws \Exception if the fixtureDefinition has no suitable parser for its source format
     */
    public function parseFixtureDefinition(FixtureDefinition $definition)
    {
        foreach ($this->definitionParsers as $definitionParser) {
            if ($definitionParser->supports($definition->name)) {
                // parse the source file
                $definition = $definitionParser->parseDefinition($definition);

                // and make sure we know how to handle all steps
                foreach ($definition->steps as $step) {
                    if (!isset($this->executors[$step->type])) {
                        return new FixtureDefinition(
                            $definition->name,
                            $definition->path,
                            $definition->rawDefinition,
                            FixtureDefinition::STATUS_INVALID,
                            array(),
                            "Can not handle fixture step of type '{$step->type}'"
                        );
                    }
                }

                return $definition;
            }
        }

        throw new \Exception("No parser available to parse fixture definition '{$definition->name}'");
    }

    /**
     * @param FixtureDefinition $definition
     * @param bool $useTransaction when set to false, no repo transaction will be used to wrap the migration
     * @param string|int|false|null $adminLogin when false, current user is used; when null, hardcoded admin account
     *
     * @throws \Exception
     */
    public function executeFixture(
        FixtureDefinition $definition,
        $useTransaction = true,
        $adminLogin = null
    ) {
        if ($definition->status == FixtureDefinition::STATUS_TO_PARSE) {
            $definition = $this->parseFixtureDefinition($definition);
        }

        if ($definition->status == FixtureDefinition::STATUS_INVALID) {
            throw new \Exception("Can not execute '{$definition->name}': {$definition->parsingError}");
        }

        $this->executeFixtureInner($definition, 0, $useTransaction, $adminLogin);
    }

    /**
     * @param FixtureDefinition $definition
     * @param int $stepOffset
     * @param bool $useTransaction when set to false, no repo transaction will be used to wrap the migration
     * @param string|int|false|null $adminLogin used only for committing db transaction if needed.
     *                                          If false or null, hardcoded admin is used
     *
     * @throws \Exception
     */
    protected function executeFixtureInner(
        FixtureDefinition $definition,
        $stepOffset = 0,
        $useTransaction = true,
        $adminLogin = null
    ) {
        echo "executeFixtureInner";
        if ($useTransaction) {
            $this->repository->beginTransaction();
        }

        $previousUserId = null;
        $steps = array_slice($definition->steps->getArrayCopy(), $stepOffset);

        try {
            $i = $stepOffset+1;
            $finalMessage = null;

            foreach ($steps as $step) {
                // we validated the fact that we have a good executor at parsing time
                $executor = $this->executors[$step->type];

                $executor->execute($step);

                $i++;
            }

            if ($useTransaction) {
                // there might be workflows or other actions happening at commit time that fail if we are not admin
                $previousUserId = $this->loginUser($this->getAdminUserIdentifier($adminLogin));

                $this->repository->commit();
                $this->loginUser($previousUserId);
            }
        } catch (\Exception $e) {
            $errorMessage = $this->getFullExceptionMessage($e) . ' in file ' . $e->getFile() . ' line ' . $e->getLine();

            if ($useTransaction) {
                try {
                    // cater to the case where the $this->repository->commit() call above throws an exception
                    if ($previousUserId) {
                        $this->loginUser($previousUserId);
                    }

                    // there is no need to become admin here, at least in theory
                    $this->repository->rollBack();
                } catch (\Exception $e2) {
                    if ($previousUserId && $e2->getMessage() == 'There is no active transaction.') {
                    } else {
                    }
                }
            }

            throw new FixtureStepExecutionException($errorMessage, $i, $e);
        }
    }

    /**
     * @param string $adminLogin
     *
     * @return int|string
     */
    protected function getAdminUserIdentifier($adminLogin)
    {
        if ($adminLogin != null) {
            return $adminLogin;
        }

        return self::ADMIN_USER_ID;
    }

    /**
     * Turns eZPublish cryptic exceptions into something more palatable for random devs
     *
     * @param \Exception $e
     *
     * @return string
     */
    protected function getFullExceptionMessage(\Exception $e)
    {
        $message = $e->getMessage();
        if (is_a($e, '\eZ\Publish\API\Repository\Exceptions\ContentTypeFieldDefinitionValidationException') ||
            is_a($e, '\eZ\Publish\API\Repository\Exceptions\LimitationValidationException') ||
            is_a($e, '\eZ\Publish\Core\Base\Exceptions\ContentFieldValidationException')
        ) {
            if (is_a($e, '\eZ\Publish\API\Repository\Exceptions\LimitationValidationException')) {
                $errorsArray = $e->getLimitationErrors();
                if ($errorsArray == null) {
                    return $message;
                }
            } elseif (is_a($e, '\eZ\Publish\Core\Base\Exceptions\ContentFieldValidationException')) {
                $errorsArray = array();
                foreach ($e->getFieldErrors() as $limitationError) {
                    // we get the 1st language
                    $errorsArray[] = reset($limitationError);
                }
            } else {
                $errorsArray = $e->getFieldErrors();
            }

            foreach ($errorsArray as $errors) {
                // sometimes error arrays are 2-level deep, sometimes 1...
                if (!is_array($errors)) {
                    $errors = array($errors);
                }
                foreach ($errors as $error) {
                    /// @todo find out what is the proper eZ way of getting a translated message for these errors
                    $translatableMessage = $error->getTranslatableMessage();
                    if (is_a($translatableMessage, '\eZ\Publish\API\Repository\Values\Translation\Plural')) {
                        $msgText = $translatableMessage->plural;
                    } else {
                        $msgText = $translatableMessage->message;
                    }

                    $message .= "\n" . $msgText . " - " . var_export($translatableMessage->values, true);
                }
            }
        }

        while (($e = $e->getPrevious()) != null) {
            $message .= "\n" . $e->getMessage();
        }

        return $message;
    }

    /**
     * @param string[] $paths
     *
     * @return FixtureDefinition[]
     */
    public function buildFixturesList($paths)
    {
        $definitions = $this->getFixturesDefinitions($paths);

        // filter away all migrations except 'to do' ones
        $toExecute = array();
        foreach ($definitions as $name => $definition) {
            $toExecute[$name] = $this->parseFixtureDefinition($definition);
        }

        ksort($toExecute);

        return $toExecute;
    }
}
