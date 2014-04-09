<?php
namespace FlyNetworks\Google\Closure;

/**
 * @author Kay Schecker <k.schecker@flynetworks.de>
 * @created 2014-04-02
 */

use TYPO3\Flow\Package\Package as BasePackage;

/**
 * The FlyNetworks Google Closure Package
 */
class Package extends BasePackage
{

	/**
	 * @var boolean
	 */
	protected $protected = TRUE;

	/**
	 * Invokes custom PHP code directly after the package manager has been initialized.
	 *
	 * @param \TYPO3\Flow\Core\Bootstrap $bootstrap The current bootstrap
	 * @return void
	 */
	public function boot(\TYPO3\Flow\Core\Bootstrap $bootstrap)
    {
        $dispatcher = $bootstrap->getSignalSlotDispatcher();

        $dispatcher->connect('TYPO3\Flow\Configuration\ConfigurationManager', 'configurationManagerReady',
            function ($configurationManager)
            {
                $configurationManager->registerConfigurationType('GoogleClosure');
            }
        );
	}
}
