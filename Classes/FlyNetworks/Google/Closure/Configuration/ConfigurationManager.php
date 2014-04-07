<?php
namespace FlyNetworks\Google\Closure\Configuration;

/**
 * @author Kay Schecker <k.schecker@flynetworks.de>
 * @created 2014-04-07
 */

use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Utility\Arrays;

/**
 * @Flow\Scope("singleton")
 */
class ConfigurationManager
{

    /**
     * @var array
     */
    protected $settings;

    /**
     * @var \TYPO3\Flow\Resource\Publishing\ResourcePublisher
     */
    protected $resourcePublisher;

    /**
     * @param string $type
     * @return mixed
     */
    public function get($key)
    {
        if (!is_array($this->settings))
            return;

        if (!array_key_exists($key, $this->settings))
            return;

        return $this->settings[$key];
    }

    /**
     * Injects the resource publisher
     *
     * @param \TYPO3\Flow\Resource\Publishing\ResourcePublisher $resourcePublisher
     * @return void
     */
    public function injectResourcePublisher(\TYPO3\Flow\Resource\Publishing\ResourcePublisher $resourcePublisher)
    {
        $this->resourcePublisher = $resourcePublisher;
    }

    /**
     * Inject the settings
     *
     * @param array $settings
     * @return void
     */
    public function injectSettings(array $settings)
    {
        if (array_key_exists('DependencyBuilder', $settings))
            $settings['DependencyBuilder'] = $this->prepareSettings($settings['DependencyBuilder']);

        if (array_key_exists('Compiler', $settings))
            $settings['Compiler'] = $this->prepareSettings($settings['Compiler']);

        $this->settings = $settings;
    }

    protected function prepareSettings(array $settings)
    {
        $defaultConfiguration = array();
        if (array_key_exists('Default', $settings))
            $defaultConfiguration = $settings['Default'];

        foreach ($settings as $configurationKey => $configuration)
        {
            if ('Default' === $configurationKey)
                continue;

            $settings[$configurationKey] = Arrays::arrayMergeRecursiveOverrule($defaultConfiguration, $configuration);
            $settings[$configurationKey] = $this->prepareKeyValueRecursive($settings[$configurationKey], $configurationKey);
        }

        $settings['Default'] = $this->prepareKeyValueRecursive($settings['Default']);
        return $settings;
    }

    protected function prepareKeyValueRecursive(array $configuration, $parentKey = null)
    {
        if (array_key_exists('ModuleOutputPath', $configuration))
            $configuration['ModuleProductionUri'] = $this->resolveResourcePath($configuration['ModuleOutputPath'], true);

        $return = array();
        foreach ($configuration as $key => $value)
        {
            if ('modules' !== $parentKey)
                $key = preg_replace('/(^|[a-z])([A-Z])/e','strtolower(strlen("\\1") ? "\\1-\\2" : "\\2")', $key);

            if (is_array($value))
                $value = $this->prepareKeyValueRecursive($value, $key);

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
     * Resolve a resource:// to an absolute or relative path.
     *
     * @param string $resourcePath
     * @param bool $relative
     *
     * @return string
     * @throws \TYPO3\Fluid\Core\ViewHelper\Exception
     */
    protected function resolveResourcePath($resourcePath)
    {
        $matches = array();
        preg_match('#resource://([^/]*)/Public/(.*)#', $resourcePath, $matches);
        if ($matches === array())
            throw new \TYPO3\Fluid\Core\ViewHelper\Exception('Resource path "' . $resourcePath . '" can\'t be resolved.', 1328543327);

        $packageKey = $matches[1];
        $path = $matches[2];

        $pathPrefix = '';
        if ('CLI' === FLOW_SAPITYPE)
            $pathPrefix = FLOW_PATH_WEB;

        return $pathPrefix . $this->resourcePublisher->getStaticResourcesWebBaseUri() . 'Packages/' . $packageKey . '/' . $path;
    }
}
