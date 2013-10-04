<?php
namespace wcf\system\event\listener;
use \wcf\system\exception\AJAXException;
use \wcf\system\exception\UserInputException;
use \wcf\system\WCF;
use \wcf\util\HeaderUtil;

/**
 * Enforces two factor auth.
 *
 * @author	Tim Düsterhus
 * @copyright	2012 - 2013 Tim Düsterhus
 * @license	BSD 3-Clause License <http://opensource.org/licenses/BSD-3-Clause>
 * @package	be.bastelstu.wcf.twofa
 * @subpackage	system.event.listener
 */
class ControllerTwoFAListener implements \wcf\system\event\IEventListener {
	/**
	 * @see	\wcf\system\event\IEventListener::execute()
	 */
	public function execute($eventObj, $className, $eventName) {
		// certain pages are always allowed
		$controller = \wcf\system\request\RequestHandler::getInstance()->getActiveRequest()->getRequestObject() ;
		switch (true) {
			case $controller instanceof \wcf\acp\action\LogoutAction:
				return;
			break;
			case $controller instanceof \wcf\page\AbstractAuthedPage:
				if (isset($_REQUEST['at'])) return;
			break;
			case $controller instanceof \wcf\form\TwofaLoginForm:
				return;
			break;
		}
		
		// 2 factor is ACP only
		if (!class_exists('\wcf\system\WCFACP', false) && TWOFA_ACP_ONLY) return;
		
		// 2 factor isn't enabled
		if (!WCF::getUser()->twofaSecret) {
			// but 2 factor is required for ACP
			if (WCF::getUser()->userID && class_exists('\wcf\system\WCFACP', false) && TWOFA_ACP_REQUIRE) {
				throw new \wcf\system\exception\NamedUserException(WCF::getLanguage()->getDynamicVariable('wcf.user.twofa.acpRequired'));
			}
			
			return;
		}
		
		// check cookie
		if (!class_exists('\wcf\system\WCFACP', false)) {
			try {
				$cookie = \wcf\util\Signer::getSignedCookie('twofa');
				if ($cookie) {
					$cookie = unserialize($cookie);
					
					if ($cookie['userID'] === WCF::getUser()->userID) {
						if ($cookie['expires'] > TIME_NOW) return;
					}
				}
			}
			catch (\wcf\system\exception\SystemException $e) { }
		}
		
		// code already was asked during this session
		if (WCF::getSession()->getVar('twofa') === WCF::getUser()->userID) return;
		
		// block AJAX completely
		if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && ($_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest')) {
			throw new \wcf\system\exception\AJAXException(WCF::getLanguage()->getDynamicVariable('wcf.user.twofa.required'), AJAXException::INSUFFICIENT_PERMISSIONS);
		}
		
		HeaderUtil::redirect(\wcf\system\request\LinkHandler::getInstance()->getLink('TwofaLogin', array('url' => $_SERVER['REQUEST_URI'])));
		exit;
	}
}
