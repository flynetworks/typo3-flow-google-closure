<?php
namespace FlyNetworks\Google\Closure\Command;

/**
 * @author Kay Schecker <k.schecker@flynetworks.de>
 * @created 2014-04-02
 */

use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Utility\Arrays;

/**
 * @Flow\Scope("singleton")
 */
class ClosureCommandController extends \TYPO3\Flow\Cli\CommandController
{

    const COMPILE_COMMAND = 'java -jar Packages/Application/FlyNetworks.Google.Closure/Resources/Private/Bin/Plovr.jar build';

    const DEPS_COMMAND = 'python Packages/Application/FlyNetworks.Google.Closure/Resources/Public/closure/bin/build/depswriter.py';

    /**
     * @var array
     */
    protected $settings;

    /**
     * @var array
     */
    protected $defaultConfiguration;

    /**
     * @Flow\Inject
     * @var \TYPO3\Flow\Resource\Publishing\ResourcePublisher
     */
    protected $resourcePublisher;


    /**
     * Inject the settings
     *
     * @param array $settings
     * @return void
     */
    public function injectSettings(array $settings)
    {
        $this->settings = $settings;
    }

    /**
     * This command compiles the configured javascript.
     *
     * @return void
     */
    public function compileCommand()
    {
        if (!array_key_exists('Compiler', $this->settings))
            return $this->outputLine('Nothing to do!');

        if (array_key_exists('Default', $this->settings['Compiler']))
            $this->defaultConfiguration = $this->settings['Compiler']['Default'];

        foreach ($this->settings['Compiler'] as $configurationKey => $configuration)
        {
            if ('Default' == $configurationKey)
                continue;

            $configuration = Arrays::arrayMergeRecursiveOverrule($this->defaultConfiguration, $configuration);
            $configuration = $this->prepareConfiguration($configuration);

            $temporaryConfigurationFile = $this->writeTemporaryJsonConfigurationFile($configuration);

            exec(self::COMPILE_COMMAND . ' ' . $temporaryConfigurationFile);
            unlink($temporaryConfigurationFile);
        }
    }

    /**
     * This command build up the dependency file.
     *
     * @return void
     */
    public function depsCommand()
    {
        if (!array_key_exists('Compiler', $this->settings))
            return $this->outputLine('Nothing to do!');

        if (array_key_exists('Default', $this->settings['Compiler']))
            $this->defaultConfiguration = $this->settings['Compiler']['Default'];

        foreach ($this->settings['Compiler'] as $configurationKey => $configuration)
        {
            if ('Default' == $configurationKey)
                continue;

            $configuration = Arrays::arrayMergeRecursiveOverrule($this->defaultConfiguration, $configuration);

            if (array_key_exists('Paths', $configuration))
            {
                $paths = $configuration['Paths'];

                foreach ($paths as $path)
                {
                    $absolutePath = $this->resolveResourcePath($path, false);
                    $relativePath = $this->resolveResourcePath($path, true);
                }
            }

            exec(self::DEPS_COMMAND . ' --root_with_prefix="' . $absolutePath . ' ' . $relativePath . '" > ' . $absolutePath . '/deps.js');
        }
    }

    /**
     * Prepares the given configuration.
     *
     * @param array $configuration
     * @return array
     */
    protected function prepareConfiguration(array $configuration)
    {
        if (array_key_exists('ModuleOutputPath', $configuration))
            $configuration['ModuleProductionUri'] = $this->resolveResourcePath($configuration['ModuleOutputPath'], true);

        $return = array();
        foreach ($configuration as $key => $value)
        {
            $key = preg_replace('/(^|[a-z])([A-Z])/e','strtolower(strlen("\\1") ? "\\1-\\2" : "\\2")', $key);

            if (is_array($value))
                $value = $this->prepareConfiguration($value);

            if (is_string($value))
            {
                if (0 === strpos($value, 'resource://'))
                    $value = $this->resolveResourcePath($value);
            }

            $return[$key] = $value;
        }

        return $return;
    }

    /**
     * Write a temporary json configuration file to the filesystem.
     *
     * @param array $configuration
     *
     * @return string
     */
    protected function writeTemporaryJsonConfigurationFile(array $configuration)
    {
        $filename = '/tmp/flynetworks.google.closure.configuration.json';

        $configurationJson = json_encode($configuration);
        file_put_contents($filename, $configurationJson);

        return $filename;
    }

    /**
     * Resolve a resource:// to an absolute or relative path.
     *
     * @param string $resourcePath
     * @param bool $relative
     *
     * @return string
     * @throws \TYPO3\Fluid\Core\ViewHelper\Exception
     */
    protected function resolveResourcePath($resourcePath, $relative = false)
    {
        $matches = array();
        preg_match('#resource://([^/]*)/Public/(.*)#', $resourcePath, $matches);
        if ($matches === array())
            throw new \TYPO3\Fluid\Core\ViewHelper\Exception('Resource path "' . $resourcePath . '" can\'t be resolved.', 1328543327);

        $packageKey = $matches[1];
        $path = $matches[2];

        $pathPrefix = '/';

        if (!$relative)
            $pathPrefix = getcwd() . '/Web/';

        return $pathPrefix . $this->resourcePublisher->getStaticResourcesWebBaseUri() . 'Packages/' . $packageKey . '/' . $path;
    }

}
