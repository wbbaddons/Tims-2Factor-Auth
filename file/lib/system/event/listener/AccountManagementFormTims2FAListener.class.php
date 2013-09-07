<?php
namespace wcf\system\event\listener;
use \wcf\system\exception\UserInputException;
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
	public $secret = '';
	public $code = '';
	
	/**
	 * @see	\wcf\system\event\IEventListener::execute()
	 */
	public function execute($eventObj, $className, $eventName) {
		require_once(WCF_DIR.'lib/system/api/2fa/PHPGangsta/GoogleAuthenticator.php');
		
		$ga = new \PHPGangsta_GoogleAuthenticator();
		
		switch ($eventName) {
			case 'readData':
				if (!$this->secret = WCF::getUser()->__get('2faSecret')) {
					if (isset($_POST['2faSecret'])) $this->secret = $_POST['2faSecret'];
					else $this->secret = $ga->createSecret();
					
					WCF::getTPL()->assign(array(
						'_2faSecret' => $this->secret,
						'_2faQR' => $ga->getQRCodeGoogleUrl(PAGE_TITLE, $this->secret)
					));
				}
			break;
			case 'validate':
				if ($this->secret = WCF::getUser()->__get('2faSecret')) {
					
				}
				else if (isset($_POST['2faSecret'])) {
					$this->secret = $_POST['2faSecret'];
					
					if (isset($_POST['2faConfirmation'])) {
						$this->code = $_POST['2faConfirmation'];
						
						if (mb_strlen($this->code) === 0) return;
						
						if (!$ga->verifyCode($this->secret, $this->code, 2)) {
							throw new UserInputException('2faCode', 'notValid');
						}
					}
				}
		}
	}
}
