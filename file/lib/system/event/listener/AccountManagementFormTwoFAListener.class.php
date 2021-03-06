<?php
namespace wcf\system\event\listener;
use \wcf\system\exception\UserInputException;
use \wcf\system\WCF;
use \wcf\util\PasswordUtil;

/**
 * Adds two factor management.
 *
 * @author	Tim Düsterhus
 * @copyright	2012 - 2013 Tim Düsterhus
 * @license	BSD 3-Clause License <http://opensource.org/licenses/BSD-3-Clause>
 * @package	be.bastelstu.wcf.twofa
 * @subpackage	system.event.listener
 */
class AccountManagementFormTwoFAListener implements \wcf\system\event\IEventListener {
	/**
	 * secret to use
	 * 
	 * @var string
	 */
	public $secret = '';
	
	/**
	 * given code
	 * 
	 * @var string
	 */
	public $code = '';
	
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
		require_once(WCF_DIR.'lib/system/api/twofa/PHPGangsta/GoogleAuthenticator.php');
		
		$ga = new \PHPGangsta_GoogleAuthenticator();
		$twofaHandler = \wcf\system\user\authentication\TwoFAHandler::getInstance();
		
		switch ($eventName) {
			case 'readData':
				if ($this->secret = WCF::getUser()->twofaSecret) {
					WCF::getTPL()->assign(array(
						'twofaDisable' => $this->disable
					));
				}
				else {
					if (isset($_POST['twofaSecret'])) $this->secret = $_POST['twofaSecret'];
					else $this->secret = $twofaHandler->generateSecret();
					
					WCF::getTPL()->assign(array(
						'twofaSecret' => $this->secret,
						'twofaQR' => $ga->getQRCodeGoogleUrl(PAGE_TITLE, $this->secret)
					));
				}
			break;
			case 'readFormParameters':
				if (isset($_POST['twofaCode'])) $this->code = $_POST['twofaCode'];
				if (isset($_POST['twofaDisable'])) $this->disable = true;
			break;
			case 'validate':
				if (WCF::getUser()->twofaSecret) {
					if ($this->disable) {
						if (mb_strlen($this->code) === 0) throw new UserInputException('twofaCode');
						if (\wcf\util\PasswordUtil::secureCompare($this->code, WCF::getUser()->twofaEmergency)) return;
						
						$twofaHandler->validate($this->code, WCF::getUser());
					}
				}
				else if (isset($_POST['twofaSecret'])) {
					$this->secret = $_POST['twofaSecret'];
					if (mb_strlen($this->code) === 0) return;
					
					$twofaHandler->validate($this->code, new \wcf\data\user\User(null, array('userID' => WCF::getUser()->userID, 'twofaSecret' => $this->secret)));
				}
			break;
			case 'saved':
				if (mb_strlen($this->code) === 0) return;
				
				if (WCF::getUser()->twofaSecret) {
					if ($this->disable) {
						$userAction = new \wcf\data\user\UserAction(array(WCF::getUser()), 'update', array(
							'data' => array(
								'twofaSecret' => null,
								'twofaEmergency' => ''
							)
						));
						$userAction->executeAction();
						WCF::getUser()->twofaSecret = null;
						
						$success = WCF::getTPL()->get('success') ?: array();
						$success[] = 'wcf.user.twofa.disable.success';
						WCF::getTPL()->assign('success', $success);
					}
				}
				else {
					$userAction = new \wcf\data\user\UserAction(array(WCF::getUser()), 'update', array(
						'data' => array(
							'twofaSecret' => $this->secret,
							'twofaEmergency' => $emergency = PasswordUtil::getRandomPassword(16)
						)
					));
					$userAction->executeAction();
					WCF::getUser()->twofaSecret = $this->secret;
					WCF::getSession()->register('twofa', WCF::getUser()->userID);
					
					$success = WCF::getTPL()->get('success') ?: array();
					$success[] = 'wcf.user.twofa.enable.success';
					WCF::getTPL()->assign(array(
						'success' => $success,
						'twofaEmergency' => $emergency
					));
				}
			break;
		}
	}
}
