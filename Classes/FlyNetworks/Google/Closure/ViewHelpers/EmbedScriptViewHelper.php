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
 * The scripts sources are taken from the Settings.yaml "Compiler" or "DependencyBuilder" configuration.
 * The render mode is specified by the "EmbedScriptMode" configuration option.
 */
class EmbedScriptViewHelper extends AbstractViewHelper
{
    /**
     * @Flow\Inject
     * @var \FlyNetworks\Google\Closure\Configuration\ConfigurationManager
     */
    protected $configurationManager;

    /**
     * Render the "Script" tag(s)
     *
     * @param string $compilerKey
     * @return string "script"-Tag(s).
     * @api
     */
    public function render($compilerKey)
    {
        $embedScriptMode = $this->configurationManager->get('EmbedScriptMode');

        switch ($embedScriptMode)
        {
            case 'compiled':
                return $this->getEmbedCodeCompiled($compilerKey);
            case 'dependency':
                return $this->getEmbedCodeDependency($compilerKey);
        }
    }

    protected function getEmbedCodeCompiled($compilerKey)
    {
        $compilerSettings = $this->configurationManager->get('Compiler');
        if (empty($compilerSettings))
            return;

        if (!array_key_exists($compilerKey, $compilerSettings))
            return;

        $compilerKeySettings = $compilerSettings[$compilerKey];

        $return = '';
        foreach ($compilerKeySettings['modules'] as $module => $moduleConfiguration)
            $return .= $this->getEmbedCodeBySrc(sprintf($compilerSettings[$compilerKey]['module-output-path'], $module));

        return $return;
    }

    protected function getEmbedCodeDependency($compilerKey)
    {
        $compilerSettings = $this->configurationManager->get('Compiler');
        if (empty($compilerSettings))
            return;

        $dependencyBuilderSettings = $this->configurationManager->get('DependencyBuilder');
        if (empty($dependencyBuilderSettings))
            return;

        $dependencyBuilderConfiguration = $dependencyBuilderSettings['Default'];
        if (array_key_exists($compilerKey, $dependencyBuilderSettings))
            $dependencyBuilderConfiguration = $dependencyBuilderSettings[$compilerKey];

        if (!array_key_exists($compilerKey, $compilerSettings))
            return;

        $compilerKeySettings = $compilerSettings[$compilerKey];
        $dependencyFile = $dependencyBuilderConfiguration['output-file-name'];

        $return = $this->getEmbedCodeBySrc($compilerKeySettings['closure-library'] . '/base.js');


        foreach ($compilerKeySettings['modules'] as $module => $moduleConfiguration)
        {
            if (array_key_exists('inputs', $moduleConfiguration))
            {
                $inputs = $moduleConfiguration['inputs'];

                if (!is_array($inputs))
                    $inputs = array($inputs);

                foreach ($inputs as $input)
                   $return .= $this->getEmbedCodeBySrc($input);
            }
        }

        if (array_key_exists('paths', $compilerKeySettings))
        {
            $paths = $compilerKeySettings['paths'];
            foreach ($paths as $path)
                $return .= $this->getEmbedCodeBySrc($path . $dependencyFile);
        }

        return $return;
    }

    protected function getEmbedCodeBySrc($src)
    {
        return '<script type="text/javascript" src="' . $src . '"></script>' . PHP_EOL;
    }
}
