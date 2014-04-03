<?php
namespace FlyNetworks\Google\Closure\Command;

/**
 * @author Kay Schecker <k.schecker@netzbewegung.com>
 * @created 2014-04-02
 */

use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Exception;

/**
 * @Flow\Scope("singleton")
 */
class ClosureCommandController extends \TYPO3\Flow\Cli\CommandController
{

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
     * This command compiles the javascript into one file.
     *
     * @return void
     */
    public function compileCommand()
    {
        if (!array_key_exists('Configuration', $this->settings))
            return $this->outputLine('Nothing to do!');

        $this->defaultConfiguration = $this->readClosureConfigurationByKey('Default');

        foreach ($this->settings['Configuration'] as $configurationKey => $configurationFile)
        {
            if ('Default' == $configurationKey)
                continue;

            $configuration = $this->readClosureConfigurationFile($configurationFile, $this->defaultConfiguration);
            $temporaryConfigurationFile = $this->writeTemporaryJsonConfigurationFile($configuration);

            exec('java -jar Packages/Application/FlyNetworks.Google.Closure/Resources/Private/Bin/Plovr.jar build ' . $temporaryConfigurationFile);
            unlink($temporaryConfigurationFile);
        }
    }

    /**
     * Reads an merge a configuration specified by the configurationKey and the parentConfiguration.
     *
     * @param $configurationKey
     * @param array $parentConfiguration
     *
     * @return array
     */
    protected function readClosureConfigurationByKey($configurationKey, array $parentConfiguration = array())
    {
        if (!array_key_exists($configurationKey, $this->settings['Configuration']))
            return array();

        $configurationFile = $this->settings['Configuration'][$configurationKey];
        return $this->readClosureConfigurationFile($configurationFile, $parentConfiguration);
    }

    /**
     * Reads an merge a configuration specified by the path and the parentConfiguration.
     *
     * @param $configurationFile
     * @param array $parentConfiguration
     *
     * @return array
     */
    protected function readClosureConfigurationFile($configurationFile, array $parentConfiguration = array())
    {
        if (!file_exists($configurationFile) || !is_readable($configurationFile))
            return array();

        $configuration = json_decode(file_get_contents($configurationFile), true);
        $configuration = \TYPO3\Flow\Utility\Arrays::arrayMergeRecursiveOverrule($parentConfiguration, $configuration);

        if (array_key_exists('module-output-path', $configuration))
            $configuration['module-production-uri'] = $this->resolveResourcePath($configuration['module-output-path'], true);

        array_walk_recursive($configuration, function(&$value){

            if (0 === strpos($value, 'resource://'))
                $value = $this->resolveResourcePath($value);

        });

        return $configuration;
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