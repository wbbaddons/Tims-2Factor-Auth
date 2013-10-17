<?php
namespace wcf\system\event\listener;

/**
 * Vaporizes unneeded data.
 *
 * @author 	Tim Düsterhus
 * @copyright	2012-2013 Tim Düsterhus
 * @license	BSD 3-Clause License <http://opensource.org/licenses/BSD-3-Clause>
 * @package	be.bastelstu.wcf.twofa
 * @subpackage	system.event.listener
 */
class DailyCleanUpCronjobExecuteTwofaCleanUpListener implements \wcf\system\event\IEventListener {
	/**
	 * @see	\wcf\system\event\IEventListener::execute()
	 */
	public function execute($eventObj, $className, $eventName) {
		// clean up blacklisted codes
		$sql = "DELETE FROM	wcf".WCF_N."_user_twofa_blacklist
			WHERE		time < ?";
		$statement = \wcf\system\WCF::getDB()->prepareStatement($sql);
		$statement->execute(array(
			(TIME_NOW - 86400 * 30)
		));
	}
}
