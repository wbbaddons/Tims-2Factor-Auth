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
 * @package	be.bastelstu.wcf.github
 * @subpackage	system.event.listener
 */
class AccountManagementFormTims2FAListener implements \wcf\system\event\IEventListener {
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
	 * should 2fa be disabled
	 * 
	 * @var boolean
	 */
	public $disable = 0;
	
	/**
	 * user object to use
	 * 
	 * @var \wcf\data\user\User
	 */
	public $user = null;
	
	/**
	 * @see	\wcf\system\event\IEventListener::execute()
	 */
	public function execute($eventObj, $className, $eventName) {
		require_once(WCF_DIR.'lib/system/api/2fa/PHPGangsta/GoogleAuthenticator.php');
		
		$ga = new \PHPGangsta_GoogleAuthenticator();
		if ($this->user === null) $this->user = WCF::getUser();
		
		switch ($eventName) {
			case 'readData':
				if ($this->secret = $this->user->__get('2faSecret')) {
					WCF::getTPL()->assign(array(
						'_2faDisable' => $this->disable
					));
				}
				else {
					if (isset($_POST['2faSecret'])) $this->secret = $_POST['2faSecret'];
					else $this->secret = $ga->createSecret();
					
					WCF::getTPL()->assign(array(
						'_2faSecret' => $this->secret,
						'_2faQR' => $ga->getQRCodeGoogleUrl(PAGE_TITLE, $this->secret)
					));
				}
			break;
			case 'readFormParameters':
				if (isset($_POST['2faCode'])) $this->code = $_POST['2faCode'];
				if (isset($_POST['2faDisable'])) $this->disable = true;
			break;
			case 'validate':
				if ($this->secret = $this->user->__get('2faSecret')) {
					if ($this->disable) {
						if (mb_strlen($this->code) === 0) throw new UserInputException('2faCode');
						
						if (!$ga->verifyCode($this->secret, $this->code, 2)) {
							throw new UserInputException('2faCode', 'notValid');
						}
					}
				}
				else if (isset($_POST['2faSecret'])) {
					$this->secret = $_POST['2faSecret'];
					if (mb_strlen($this->code) === 0) return;
					
					if (!$ga->verifyCode($this->secret, $this->code, 2)) {
						throw new UserInputException('2faCode', 'notValid');
					}
				}
			break;
			case 'save':
				if (mb_strlen($this->code) === 0) return;
				
				if ($this->user->__get('2faSecret')) {
					if ($this->disable) {
						$userAction = new \wcf\data\user\UserAction(array(WCF::getUser()), 'update', array(
							'data' => array(
								'2faSecret' => null
							)
						));
						$userAction->executeAction();
						
						$success = WCF::getTPL()->get('success') ?: array();
						$success[] = 'wcf.user.2fa.disable.success';
						WCF::getTPL()->assign('success', $success);
					}
				}
				else {
					$userAction = new \wcf\data\user\UserAction(array(WCF::getUser()), 'update', array(
						'data' => array(
							'2faSecret' => $this->secret
						)
					));
					$userAction->executeAction();
					
					$success = WCF::getTPL()->get('success') ?: array();
					$success[] = 'wcf.user.2fa.enable.success';
					WCF::getTPL()->assign('success', $success);
				}
				
				$this->user = new \wcf\data\user\User($this->user->userID);
			break;
		}
	}
}
