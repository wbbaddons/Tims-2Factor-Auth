<?php
namespace wcf\system\event\listener;
use \wcf\system\exception\AJAXException;
use \wcf\system\exception\UserInputException;
use \wcf\system\WCF;

/**
 * Enforces two factor auth.
 *
 * @author	Tim DÃ¼sterhus
 * @copyright	2012 - 2013 Tim DÃ¼sterhus
 * @license	Creative Commons Attribution-NonCommercial-ShareAlike <http://creativecommons.org/licenses/by-nc-sa/3.0/legalcode>
 * @package	be.bastelstu.wcf.twofa
 * @subpackage	system.event.listener
 */
class ControllerTwoFAListener implements \wcf\system\event\IEventListener {
	/**
	 * @see	\wcf\system\event\IEventListener::execute()
	 */
	public function execute($eventObj, $className, $eventName) {
		if (!WCF::getUser()->twofaSecret) return;
		if (WCF::getSession()->getVar('twofa') === true) return;
		switch (ltrim(\wcf\system\request\RequestHandler::getInstance()->getActiveRequest()->getClassName(), '\\')) {
			case 'wcf\action\LogoutAction':
				return;
		}
		require_once(WCF_DIR.'lib/system/api/twofa/PHPGangsta/GoogleAuthenticator.php');
		
		$ga = new \PHPGangsta_GoogleAuthenticator();
		$twofaHandler = \wcf\system\twofa\TwoFAHandler::getInstance();
		
		if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && ($_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest')) {
			throw new \wcf\system\exception\AJAXException(WCF::getLanguage()->getDynamicVariable('wcf.user.twofa.required'), AJAXException::INSUFFICIENT_PERMISSIONS);
		}
		
		$errorField = '';
		$errorType = '';
		if (isset($_POST['twofaForm'])) {
			try {
				if (!isset($_POST['twofaCode']) || mb_strlen($_POST['twofaCode']) === 0) throw new UserInputException('twofaCode');
				$twofaHandler->validate($_POST['twofaCode'], WCF::getUser());
				WCF::getSession()->register('twofa', true);
			}
			catch (\wcf\system\exception\UserInputException $e) {
				$errorField = $e->getField();
				$errorType = $e->getType();
			}
		}
		
		WCF::getTPL()->assign(array(
			'templateName' => 'twofaLogin',
			'errorField' => $errorField,
			'errorType' => $errorType
		));
		WCF::getTPL()->display('twofaLogin');
		exit;
	}
}
