<?php
namespace wcf\system\twofa;
use wcf\util\PasswordUtil;

use \wcf\system\exception\UserInputException;
use \wcf\system\WCF;

/**
 * TwoFA Handler
 *
 * @author	Tim DÃ¼sterhus
 * @copyright	2012-2013 Tim DÃ¼sterhus
 * @license	Creative Commons Attribution-NonCommercial-ShareAlike <http://creativecommons.org/licenses/by-nc-sa/3.0/legalcode>
 * @package	be.bastelstu.wcf.twofa
 * @subpackage	system.twofa
 */
class TwoFAHandler extends \wcf\system\SingletonFactory {
	/**
	 * @see \PHPGangsta_GoogleAuthenticator::verifyCode()
	 */
	const FUZZ = 2;
	
	/**
	 * @see \PHPGangsta_GoogleAuthenticator::createSecret()
	 */
	const SECRET_LENGTH = 16;
	
	/**
	 * instance of \PHPGangsta_GoogleAuthenticator
	 * @var \PHPGangsta_GoogleAuthenticator
	 */
	private $ga = null;
	
	/**
	 * Statement to check blacklist
	 * 
	 * @var \wcf\system\database\statement\PreparedStatement
	 */
	private $checkBlacklist = null;
	
	/**
	 * Statement to add to blacklist
	 *
	 * @var \wcf\system\database\statement\PreparedStatement
	 */
	private $insertBlacklist = null;
	
	/**
	 * @see \wcf\system\SingletonFactory::init()
	 */
	protected function init() {
		require_once(WCF_DIR.'lib/system/api/twofa/PHPGangsta/GoogleAuthenticator.php');
		
		$this->ga = new \PHPGangsta_GoogleAuthenticator();
		$sql = "SELECT	COUNT(*)
			FROM	wcf".WCF_N."_user_twofa_blacklist
			WHERE		time > ?
				AND	code = ?
				AND	userID = ?
			FOR UPDATE";
		$this->checkBlacklist = WCF::getDB()->prepareStatement($sql);
		
		$sql = "INSERT INTO wcf".WCF_N."_user_twofa_blacklist (time, code, userID) VALUES (?, ?, ?)";
		$this->insertBlacklist = WCF::getDB()->prepareStatement($sql);
	}
	
	/**
	 * Validates and blacklists the code for the given user.
	 * 
	 * @param	string			$code
	 * @param	\wcf\data\user\User	$user
	 */
	public function validate($code, \wcf\data\user\User $user) {
		WCF::getDB()->beginTransaction();
		try {
			$this->checkBlacklist->execute(array(TIME_NOW - (self::FUZZ + 2) * 30, $code, $user->userID));
			if ($this->checkBlacklist->fetchColumn()) throw new UserInputException('twofaCode', 'used');
			
			$currentTimeSlice = floor(time() / 30);
			
			$valid = false;
			for ($i = -self::FUZZ; $i <= self::FUZZ; $i++) {
				$calculatedCode = $this->ga->getCode($user->twofaSecret, $currentTimeSlice + $i);
				if (PasswordUtil::secureCompare($code, $calculatedCode)) {
					$valid = true;
				}
			}
			if (!$valid) throw new UserInputException('twofaCode', 'notValid');
			
			$this->insertBlacklist->execute(array(TIME_NOW, $code, $user->userID));
			WCF::getDB()->commitTransaction();
		}
		catch (UserInputException $e) {
			$this->insertBlacklist->execute(array(TIME_NOW, $code, $user->userID));
			WCF::getDB()->commitTransaction();
			
			throw $e;
		}
		catch (\Exception $e) {
			WCF::getDB()->rollbackTransaction();
			
			throw $e;
		}
	}
	
	/**
	 * Generates a secret.
	 * 
	 * @see \PHPGangsta_GoogleAuthenticator::createSecret()
	 */
	public function generateSecret() {
		static $chars = array(
			'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', //  7
			'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', // 15
			'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', // 23
			'Y', 'Z', '2', '3', '4', '5', '6', '7' // 31
		);
		
		$secret = '';
		for ($i = 0, $charCount = count($chars) - 1; $i < self::SECRET_LENGTH; $i++) {
			$secret .= $chars[PasswordUtil::secureRandomNumber(0, $charCount)];
		}
		return $secret;
	}
}
