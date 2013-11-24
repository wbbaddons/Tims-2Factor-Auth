<?php
namespace wcf\system\event\listener;
use \wcf\system\exception\UserInputException;
use \wcf\system\WCF;
use \wcf\util\PasswordUtil;

/**
 * Allows the admin to disable twofa.
 *
 * @author	Tim Düsterhus
 * @copyright	2012 - 2013 Tim Düsterhus
 * @license	BSD 3-Clause License <http://opensource.org/licenses/BSD-3-Clause>
 * @package	be.bastelstu.wcf.twofa
 * @subpackage	system.event.listener
 */
class UserEditFormTwoFAListener implements \wcf\system\event\IEventListener {
	/**
	 * should twofa be disabled
	 * 
	 * @var boolean
	 */
	public $disable = 0;
	
	/**
	 * @see	\wcf\system\event\IEventListener::execute()
	 */
	public function execute($eventObj, $className, $eventName) {
		switch ($eventName) {
			case 'assignVariables':
				WCF::getTPL()->assign(array(
					'twofaDisable' => $this->disable
				));
			break;
			case 'readFormParameters':
				if (WCF::getSession()->getPermission('admin.user.canEditPassword') && isset($_POST['twofaDisable'])) $this->disable = true;
			break;
			case 'save':
				if ($this->disable) {
					$eventObj->additionalFields['twofaSecret'] = null;
					$eventObj->additionalFields['twofaEmergency'] = null;
					$eventObj->user->twofaSecret = null;
				}
			break;
		}
	}
}
