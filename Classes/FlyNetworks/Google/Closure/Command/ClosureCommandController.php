<?php
namespace FlyNetworks\Google\Closure\Command;

/**
 * @author Kay Schecker <k.schecker@flynetworks.de>
 * @created 2014-04-02
 */

use TYPO3\Flow\Annotations as Flow;

/**
 * @Flow\Scope("singleton")
 */
class ClosureCommandController extends \TYPO3\Flow\Cli\CommandController
{
    /**
     * @Flow\Inject
     * @var \FlyNetworks\Google\Closure\Configuration\ConfigurationProvider
     */
    protected $configurationProvider;

    /**
     * Validates the whole configuration
     */
    protected function validateConfiguration()
    {
        $validationResult = $this->configurationProvider->validateConfiguration();

        if ($validationResult->hasErrors())
        {
            $errors = $validationResult->getFlattenedErrors();
            $this->outputLine('<b>%s configuration error(s) were found:</b>', array(count($errors)));

            foreach ($errors as $path => $pathErrors)
            {
                foreach ($pathErrors as $error)
                    $this->outputLine(' - %s -> %s', array($path, $error->render()));
            }

            $this->quit(1);
        }
    }

    /**
     * This command compiles the configured javascript.
     *
     * @return void
     */
    public function compileCommand()
    {
        $this->validateConfiguration();

        $configurationKeys = $this->configurationProvider->getConfigurationKeys();
        foreach ($configurationKeys as $configurationKey)
        {
            if ('Default' == $configurationKey)
                continue;

            $compilerCommand = $this->configurationProvider->getConfiguration($configurationKey . '.compiler.command');

            if (empty($compilerCommand))
            {
                $this->outputLine($configurationKey . '.compiler.command is empty!');
                $this->sendAndExit();
            }

            $compilerOptions = $this->configurationProvider->getConfiguration($configurationKey . '.compiler.options');
            if (empty($compilerOptions))
            {
                $this->outputLine($configurationKey . 'compiler.options is empty!');
                $this->sendAndExit();
            }

            $preparedCompilerOptions = array();
            foreach ($compilerOptions as $index => $value)
            {
                $index =  preg_replace('/(^|[a-z])([A-Z])/e','strtolower(strlen("\\1") ? "\\1-\\2" : "\\2")', $index);
                $preparedCompilerOptions[$index] = $value;
            }

            $temporaryFileName = FLOW_PATH_WEB . 'flynetworks.google.closure.configuration.json';
            file_put_contents($temporaryFileName, json_encode($preparedCompilerOptions));
            exec($compilerCommand . ' ' . $temporaryFileName);
            unlink($temporaryFileName);
        }
    }

    /**
     * This command build up the dependency file.
     *
     * @return void
     */
    public function dependencyCommand()
    {
        $this->validateConfiguration();

        $configurationKeys = $this->configurationProvider->getConfigurationKeys();
        foreach ($configurationKeys as $configurationKey)
        {
            if ('Default' == $configurationKey)
                continue;

            $dependencyCommand = $this->configurationProvider->getConfiguration($configurationKey . '.dependency.command');
            if (empty($dependencyCommand))
            {
                $this->outputLine($configurationKey . 'dependency.command is empty!');
                $this->sendAndExit();
            }

            $dependencyOutputFileName = $this->configurationProvider->getConfiguration($configurationKey . '.dependency.outputFileName');
            if (empty($dependencyOutputFileName))
            {
                $this->outputLine($configurationKey . 'dependency.outputFileName is empty!');
                $this->sendAndExit();
            }

            $compilerOptions = $this->configurationProvider->getConfiguration($configurationKey . '.compiler.options');
            if (empty($compilerOptions))
            {
                $this->outputLine($configurationKey . 'compiler.options is empty!');
                $this->sendAndExit();
            }

            $paths = $this->configurationProvider->getConfiguration($configurationKey . '.compiler.options.paths');
            foreach ($paths as $path)
                exec($dependencyCommand . ' --root_with_prefix="' . FLOW_PATH_WEB . $path . ' ' . $path . '" > ' . FLOW_PATH_WEB . $path . $dependencyOutputFileName);
        }
    }
}
