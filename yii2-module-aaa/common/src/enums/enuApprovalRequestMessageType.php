<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\aaa\common\enums;

use shopack\base\common\base\BaseEnum;

abstract class enuApprovalRequestMessageType extends BaseEnum
{
  const EmailApproval            = 'emailApproval';
  const MobileApproval           = 'mobileApproval';
  const EmailApprovalForLogin    = 'emailApprovalForLogin';
  const MobileApprovalForLogin   = 'mobileApprovalForLogin';
  const EmailApproved            = 'emailApproved';
  const MobileApproved           = 'mobileApproved';

	public static $messageCategory = 'aaa';

	public static $list = [
		self::EmailApproval            => 'emailApproval',
		self::MobileApproval           => 'mobileApproval',
		self::EmailApprovalForLogin    => 'emailApprovalForLogin',
		self::MobileApprovalForLogin   => 'mobileApprovalForLogin',
		self::EmailApproved            => 'emailApproved',
		self::MobileApproved           => 'mobileApproved',
	];

};
