<?php
namespace FlyNetworks\Google\Closure\Configuration;

/**
 * @author Kay Schecker <k.schecker@flynetworks.de>
 * @created 2014-04-02
 */

use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Utility\Arrays;

/**
 * @Flow\Scope("singleton")
 */
class ConfigurationProvider
{

    const CONFIGURATION_TYPE = 'GoogleClosure';

    /**
     * @var array
     */
    protected $configuration;

    /**
     * @var \TYPO3\Flow\Configuration\ConfigurationSchemaValidator
     * @Flow\Inject
     */
    protected $configurationSchemaValidator;

    /**
     * @var \TYPO3\Flow\Resource\Publishing\ResourcePublisher
     */
    protected $resourcePublisher;

    /**
     * This array store all extended configurations for internal purposes only.
     *
     * @var array
     */
    protected $extendedConfigurations = array('Default');

    /**
     * Constructs a new ConfigurationProvider
     *
     * @param \TYPO3\Flow\Configuration\ConfigurationManager $configurationManager
     * @param \TYPO3\Flow\Resource\Publishing\ResourcePublisher $resourcePublisher
     */
    public function __construct(\TYPO3\Flow\Configuration\ConfigurationManager $configurationManager, \TYPO3\Flow\Resource\Publishing\ResourcePublisher $resourcePublisher)
    {
        $this->resourcePublisher = $resourcePublisher;
        $this->configuration = $configurationManager->getConfiguration(self::CONFIGURATION_TYPE);
        $this->prepareConfiguration();
    }

    /**
     * Prepares the given configuration for inheritance and resource:// urls
     *
     * @return array The prepared configuration
     */
    protected function prepareConfiguration()
    {
        array_walk_recursive($this->configuration, function(&$value){
            if (is_string($value))
            {
                if (0 === strpos($value, 'resource://'))
                    $value = $this->resolveResourcePath($value);
            }
        });

        //extend configurations
        foreach ($this->configuration as $index => $subConfiguration)
            $this->configuration[$index] = $this->extendConfiguration($index, $subConfiguration);
    }

    /**
     * Resolve a resource:// to an relative path.
     *
     * @param string $resourcePath
     * @return string
     */
    protected function resolveResourcePath($resourcePath)
    {
        $matches = array();
        preg_match('#resource://([^/]*)/Public/(.*)#', $resourcePath, $matches);
        if ($matches === array())
            return; //@todo throw Exception here

        $packageKey = $matches[1];
        $path = $matches[2];

        return $this->resourcePublisher->getStaticResourcesWebBaseUri() . 'Packages/' . $packageKey . '/' . $path;
    }

    /**
     * Extends the given subConfiguration
     *
     * @param string $ownConfigurationKey
     * @param array $subConfiguration
     * @return array The extended subConfiguration
     */
    protected function extendConfiguration($ownConfigurationKey, array $subConfiguration)
    {
        $parentConfiguration = 'Default';
        if (array_key_exists('extends', $subConfiguration))
            $parentConfiguration = $subConfiguration['extends'];

        if ('__NONE__' === $parentConfiguration)
            return $subConfiguration;

        if (in_array($ownConfigurationKey, $this->extendedConfigurations))
            return $subConfiguration;

        if (!array_key_exists($parentConfiguration, $this->configuration))
            return $subConfiguration; //@todo throw Exception here

        $parentSubConfiguration = $this->extendConfiguration($parentConfiguration, $this->configuration[$parentConfiguration]);
        $this->extendedConfigurations[] = $parentConfiguration;

        return Arrays::arrayMergeRecursiveOverrule($parentSubConfiguration, $subConfiguration);
    }

    /**
     * Returns all configured configurationKeys
     *
     * @return array
     */
    public function getConfigurationKeys()
    {
       return array_keys($this->configuration);
    }

    /**
     * Returns the specified configuration by following the inheritance.
     *
     * @param string $configurationPath The path inside the configuration to fetch
     * @return array The configuration
     */
    public function getConfiguration($configurationPath)
    {
        if (NULL !== $configurationPath && NULL !== $this->configuration)
            return (Arrays::getValueByPath($this->configuration, $configurationPath));

        return $this->configuration;
    }

    /**
     * Validates the complete configuration
     *
     * @return \TYPO3\Flow\Error\Result the result of the validation
     */
    public function validateConfiguration()
    {
        return $this->configurationSchemaValidator->validate(self::CONFIGURATION_TYPE);
    }
}