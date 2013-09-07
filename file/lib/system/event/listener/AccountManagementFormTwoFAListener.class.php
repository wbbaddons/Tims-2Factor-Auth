<?php
namespace wcf\system\event\listener;
use \wcf\system\exception\UserInputException;
use \wcf\system\WCF;

/**
 * Saves token in management.
 *
 * @author	Tim Düsterhus
 * @copyright	2012 - 2013 Tim Düsterhus
 * @license	Creative Commons Attribution-NonCommercial-ShareAlike <http://creativecommons.org/licenses/by-nc-sa/3.0/legalcode>
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
		
		switch ($eventName) {
			case 'readData':
				if ($this->secret = WCF::getUser()->twofaSecret) {
					WCF::getTPL()->assign(array(
						'twofaDisable' => $this->disable
					));
				}
				else {
					if (isset($_POST['twofaSecret'])) $this->secret = $_POST['twofaSecret'];
					else $this->secret = $ga->createSecret();
					
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
				if ($this->secret = WCF::getUser()->twofaSecret) {
					if ($this->disable) {
						if (mb_strlen($this->code) === 0) throw new UserInputException('twofaCode');
						
						if (!$ga->verifyCode($this->secret, $this->code, 2)) {
							throw new UserInputException('twofaCode', 'notValid');
						}
					}
				}
				else if (isset($_POST['twofaSecret'])) {
					$this->secret = $_POST['twofaSecret'];
					if (mb_strlen($this->code) === 0) return;
					
					if (!$ga->verifyCode($this->secret, $this->code, 2)) {
						throw new UserInputException('twofaCode', 'notValid');
					}
				}
			break;
			case 'save':
				if (mb_strlen($this->code) === 0) return;
				
				if ($this->user->__get('twofaSecret')) {
					if ($this->disable) {
						$userAction = new \wcf\data\user\UserAction(array(WCF::getUser()), 'update', array(
							'data' => array(
								'twofaSecret' => null
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
							'twofaSecret' => $this->secret
						)
					));
					$userAction->executeAction();
					WCF::getUser()->twofaSecret = $this->secret;
					
					$success = WCF::getTPL()->get('success') ?: array();
					$success[] = 'wcf.user.twofa.enable.success';
					WCF::getTPL()->assign('success', $success);
				}
			break;
		}
	}
}
