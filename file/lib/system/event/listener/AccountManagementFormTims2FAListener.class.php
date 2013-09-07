<?php
namespace wcf\system\event\listener;
use \wcf\system\WCF;

/**
 * Saves token in management.
 *
 * @author 	Tim Düsterhus
 * @copyright	2012 - 2013 Tim Düsterhus
 * @license	Creative Commons Attribution-NonCommercial-ShareAlike <http://creativecommons.org/licenses/by-nc-sa/3.0/legalcode>
 * @package	be.bastelstu.wcf.github
 * @subpackage	system.event.listener
 */
class AccountManagementFormTims2FAListener implements \wcf\system\event\IEventListener {
	/**
	 * @see	\wcf\system\event\IEventListener::execute()
	 */
	public function execute($eventObj, $className, $eventName) {
		require_once(WCF_DIR.'lib/system/api/2fa/PHPGangsta/GoogleAuthenticator.php');
		
		$ga = new \PHPGangsta_GoogleAuthenticator();
		WCF::getTPL()->assign(array(
			'_2faSecret' => $secret = $ga->createSecret(),
			'_2faQR' => $ga->getQRCodeGoogleUrl(PAGE_TITLE, $secret)
		));
	}
}
