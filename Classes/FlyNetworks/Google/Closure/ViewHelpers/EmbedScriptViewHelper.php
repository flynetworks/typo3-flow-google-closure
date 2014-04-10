<?php
namespace FlyNetworks\Google\Closure\ViewHelpers;

/**
 * @author Kay Schecker <k.schecker@flynetworks.de>
 * @created 2014-04-07
 */

use TYPO3\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3\Flow\Annotations as Flow;

/**
 * View helper which creates one or few <script type="text/javascript" tag(s).
 * The scripts sources are taken from the GoogleClosure.yaml "compiler" or "dependency" configuration.
 * The render mode is specified by the "embedMode" configuration option.
 */
class EmbedScriptViewHelper extends AbstractViewHelper
{
    /**
     * @Flow\Inject
     * @var \FlyNetworks\Google\Closure\Configuration\ConfigurationProvider
     */
    protected $configurationProvider;

    /**
     * @var string
     */
    protected $configurationKey;

    /**
     * @param string $configurationKey
     * @return string
     */
    public function render($configurationKey)
    {
        $this->configurationKey = $configurationKey;

        switch ($this->getConfiguration('embedMode'))
        {
            case 'compiled':
                $files = $this->getCompilerFiles();
                break;
            case 'dependency':
                $files = $this->getDependencyFiles();
                break;
        }

        $return = array();
        foreach ($files as $file)
            $return[] = '<script type="text/javascript" src="' . $file . '"></script>';

        return implode(PHP_EOL, $return);
    }

    /**
     * @param string $configurationPath
     * @return array
     */
    protected function getConfiguration($configurationPath)
    {
        return $this->configurationProvider->getConfiguration($this->configurationKey . '.' . $configurationPath);
    }

    /**
     * @return array
     */
    protected function getCompilerFiles()
    {
        $return = array();
        $modules = $this->getConfiguration('compiler.options.modules');

        if (!is_array($modules))
            return $return;

        foreach ($modules as $module => $moduleConfiguration)
            $return[] = sprintf($this->getConfiguration('compiler.options.moduleOutputPath'), $module);

        return $return;
    }

    /**
     * @return array
     */
    protected function getDependencyFiles()
    {
        $return = array($this->getConfiguration('compiler.options.closureLibrary') . 'base.js');

        $inputs = $this->getConfiguration('compiler.options.inputs');
        if (is_array($inputs))
        {
            foreach ($inputs as $input)
                $return[] = $input;
        }

        $modules = $this->getConfiguration('compiler.options.modules');
        if (is_array($modules))
        {
            foreach ($modules as $moduleConfiguration)
            {
                foreach ($moduleConfiguration['inputs'] as $input)
                    $return[] = $input;
            }
        }

        $paths = $this->getConfiguration('compiler.options.paths');
        if (is_array($paths))
        {
            foreach ($paths as $path)
                $return[] = $path . $this->getConfiguration('dependency.outputFileName');
        }

        return $return;
    }
}
