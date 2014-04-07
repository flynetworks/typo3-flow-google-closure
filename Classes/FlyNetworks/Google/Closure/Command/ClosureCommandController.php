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
     * @var \FlyNetworks\Google\Closure\Configuration\ConfigurationManager
     */
    protected $configurationManager;

    /**
     * This command compiles the configured javascript.
     *
     * @return void
     */
    public function compileCommand()
    {
        $compileCommand = $this->configurationManager->get('CompileCommand');
        if (empty($compileCommand))
        {
            $this->outputLine('CompileCommand is empty!');
            $this->sendAndExit();
        }

        $compilerSettings = $this->configurationManager->get('Compiler');
        if (empty($compilerSettings))
        {
            $this->outputLine('Nothing to do!');
            $this->sendAndExit();
        }

        foreach ($compilerSettings as $configurationKey => $configuration)
        {
            if ('Default' == $configurationKey)
                continue;

            $temporaryFileName = FLOW_PATH_DATA . 'flynetworks.google.closure.configuration.json';
            file_put_contents($temporaryFileName, json_encode($configuration));
            exec($compileCommand . ' ' . $temporaryFileName);
            unlink($temporaryFileName);
        }
    }

    /**
     * This command build up the dependency file.
     *
     * @return void
     */
    public function depsCommand()
    {
        $dependencyBuilderCommand = $this->configurationManager->get('DependencyBuilderCommand');
        if (empty($dependencyBuilderCommand))
        {
            $this->outputLine('DependencyBuilderCommand is empty!');
            $this->sendAndExit();
        }

        $compilerSettings = $this->configurationManager->get('Compiler');
        if (empty($compilerSettings))
        {
            $this->outputLine('Nothing to do!');
            $this->sendAndExit();
        }

        $dependencyBuilderSettings = $this->configurationManager->get('DependencyBuilder');
        if (empty($dependencyBuilderSettings))
        {
            $this->outputLine('Nothing to do!');
            $this->sendAndExit();
        }

        foreach ($compilerSettings as $configurationKey => $configuration)
        {
            if ('Default' == $configurationKey)
                continue;

            $dependencyBuilderConfiguration = $dependencyBuilderSettings['Default'];
            if (array_key_exists($configurationKey, $dependencyBuilderSettings))
                $dependencyBuilderConfiguration = $dependencyBuilderSettings[$configurationKey];

            if (array_key_exists('paths', $configuration))
            {
                $paths = $configuration['paths'];
                foreach ($paths as $absolutePath)
                {
                    $relativePath = str_replace(FLOW_PATH_WEB, '/', $absolutePath);
                    $dependencyFile = $absolutePath . $dependencyBuilderConfiguration['output-file-name'];
                    exec($dependencyBuilderCommand . ' --root_with_prefix="' . $absolutePath . ' ' . $relativePath . '" > ' . $dependencyFile);
                }
            }
        }
    }
}
