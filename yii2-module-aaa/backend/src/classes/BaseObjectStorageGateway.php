<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\aaa\backend\classes;

use Yii;
use shopack\base\common\base\BaseGateway;

class BaseObjectStorageGateway extends BaseGateway
{
	const PARAM_TYPE = 'type';

	const RESTRICTION_AllowedFileTypes   = 'AllowedFileTypes';
	const RESTRICTION_AllowedMimeTypes   = 'AllowedMimeTypes';
	const RESTRICTION_AllowedMinFileSize = 'AllowedMinFileSize';
	const RESTRICTION_AllowedMaxFileSize = 'AllowedMaxFileSize';
	const RESTRICTION_MaxFilesCount      = 'MaxFilesCount';
	const RESTRICTION_MaxFilesSize       = 'MaxFilesSize';

	const USAGE_CreatedFilesCount        = 'CreatedFilesCount';
	const USAGE_CreatedFilesSize         = 'CreatedFilesSize';
	const USAGE_DeletedFilesCount        = 'DeletedFilesCount';
	const USAGE_DeletedFilesSize         = 'DeletedFilesSize';
	const USAGE_LastActionTime           = 'LastActionTime';

	public function getRestrictionsSchema()
	{
		return array_merge([
			[
				'id' => self::RESTRICTION_AllowedFileTypes,
				'type' => 'string',
				'label' => 'Allowed File Types',
				// 'mandatory' => 1,
			],
			[
				'id' => self::RESTRICTION_AllowedMimeTypes,
				'type' => 'string',
				'label' => 'Allowed Mime Types',
				// 'mandatory' => 1,
			],
			[
				'id' => self::RESTRICTION_AllowedMinFileSize,
				'type' => 'string',
				'label' => 'Allowed Min File Size',
				// 'mandatory' => 1,
				'format' => 'decimal',
				'format-suffix' => 'bytes',
			],
			[
				'id' => self::RESTRICTION_AllowedMaxFileSize,
				'type' => 'string',
				'label' => 'Allowed Max File Size',
				// 'mandatory' => 1,
				'format' => 'decimal',
				'format-suffix' => 'bytes',
			],
			[
				'id' => self::RESTRICTION_MaxFilesCount,
				'type' => 'string',
				'label' => 'Max Files Count',
				// 'mandatory' => 1,
				'format' => 'decimal',
			],
			[
				'id' => self::RESTRICTION_MaxFilesSize,
				'type' => 'string',
				'label' => 'Max Files Size',
				// 'mandatory' => 1,
				'format' => 'decimal',
				'format-suffix' => 'bytes',
			],
		], parent::getRestrictionsSchema());
	}

	public function getUsagesSchema()
	{
		return array_merge([
			[
				'id' => self::USAGE_CreatedFilesCount,
				'type' => 'string',
				'label' => 'Created Files Count',
				'format' => 'decimal',
			],
			[
				'id' => self::USAGE_CreatedFilesSize,
				'type' => 'string',
				'label' => 'Created Files Size',
				'format' => 'decimal',
				'format-suffix' => 'bytes',
			],
			[
				'id' => self::USAGE_DeletedFilesCount,
				'type' => 'string',
				'label' => 'Deleted Files Count',
			],
			[
				'id' => self::USAGE_DeletedFilesSize,
				'type' => 'string',
				'label' => 'Deleted Files Size',
			],
			[
				'id' => self::USAGE_LastActionTime,
				'type' => 'string',
				'label' => 'Last Action Time',
			],
		], parent::getUsagesSchema());
	}

}
