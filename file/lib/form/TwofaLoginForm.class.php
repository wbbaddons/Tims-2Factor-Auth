<?php
namespace wcf\form;
use \wcf\system\exception\AJAXException;
use \wcf\system\exception\UserInputException;
use \wcf\system\WCF;
use \wcf\util\HeaderUtil;

/**
 * Shows the Two-Factor auth form
 *
 * @author	Maximilian Mader
 * @copyright	2012 - 2013 Tim Düsterhus
 * @license	BSD 3-Clause License <http://opensource.org/licenses/BSD-3-Clause>
 * @package	be.bastelstu.wcf.twofa
 * @subpackage	form
 */
class TwofaLoginForm extends RecaptchaForm {
	/*
	 * String that contains the Two-Factor Code entered by the user
	 * @var string
	 */
	public $twofaCode = '';
	
	/**
	 * @see	wcf\page\IPage::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		// 2 factor is ACP only
		if (!class_exists('\wcf\system\WCFACP', false) && TWOFA_ACP_ONLY) throw new \wcf\system\exception\IllegalLinkException();
		
		// 2 factor isn't enabled
		if (!WCF::getUser()->twofaSecret) {
			// but 2 factor is required for ACP
			if (WCF::getUser()->userID && class_exists('\wcf\system\WCFACP', false) && TWOFA_ACP_REQUIRE) {
				throw new \wcf\system\exception\NamedUserException('Twofa required');
			}
			
			throw new \wcf\system\exception\IllegalLinkException();
		}
		
		// code already was asked during this session
		if (WCF::getSession()->getVar('twofa') === WCF::getUser()->userID) throw new \wcf\system\exception\IllegalLinkException();
		
		// force captcha (if enabled) for security reasons, even if it hase been solved before
		$this->useCaptcha = MODULE_SYSTEM_RECAPTCHA;
	}
	
	/**
	 * @see	wcf\form\IForm::readFormParameters()
	 */
	public function readFormParameters() {
		parent::readFormParameters();
		
		if (isset($_POST['twofaCode'])) $this->twofaCode = $_POST['twofaCode'];
	}
	
	/**
	 * @see	wcf\form\IForm::validate()
	 */
	public function validate() {
		parent::validate();
		
		if (mb_strlen($this->twofaCode) === 0) throw new UserInputException('twofaCode');
		
		require_once(WCF_DIR.'lib/system/api/twofa/PHPGangsta/GoogleAuthenticator.php');
		
		$ga = new \PHPGangsta_GoogleAuthenticator();
		$twofaHandler = \wcf\system\user\authentication\TwoFAHandler::getInstance();
		
		if (\wcf\util\PasswordUtil::secureCompare($this->twofaCode, WCF::getUser()->twofaEmergency)) {
			// emergency code was used, disable 2 factor
			WCF::getSession()->register('twofa', WCF::getUser()->userID);
			$userAction = new \wcf\data\user\UserAction(array(WCF::getUser()), 'update', array(
				'data' => array(
					'twofaSecret' => null
				)
			));
			$userAction->executeAction();
			WCF::getUser()->twofaSecret = null;
			
			HeaderUtil::delayedRedirect(\wcf\system\request\LinkHandler::getInstance()->getLink('AccountManagement'), WCF::getLanguage()->get('wcf.user.twofa.emergency.success'));
		}
		else {
			$twofaHandler->validate($this->twofaCode, WCF::getUser());
			WCF::getSession()->register('twofa', WCF::getUser()->userID);
			
			// remember auth
			\wcf\util\Signer::setSignedCookie('twofa', serialize(array(
				'userID' => WCF::getUser()->userID,
				'expires' => TIME_NOW + TWOFA_REASK_PERIOD * 86400
			)), TIME_NOW + TWOFA_REASK_PERIOD * 86400);
			
			HeaderUtil::redirect($_REQUEST['url']);
		}
	}
	
	/**
	 * @see	wcf\page\IPage::assignVariables()
	 */
	public function assignVariables() {
		// force captcha (if enabled) for security reasons, even if it hase been solved before
		$this->useCaptcha = MODULE_SYSTEM_RECAPTCHA;
		
		WCF::getTPL()->assign(array(
			'url' => $_REQUEST['url']
		));
		
		parent::assignVariables();
	}
	
	/**
	 * @see	wcf\form\IForm::save()
	 */
	public function save() {
		parent::save();
		
		exit;
	}
}
