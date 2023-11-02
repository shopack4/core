<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\aaa\backend\models;

use shopack\aaa\backend\classes\AAAActiveRecord;

class UserAccessGroupModel extends AAAActiveRecord
{
  use \shopack\aaa\common\models\UserAccessGroupModelTrait;

	public static function tableName()
	{
		return '{{%AAA_User_AccessGroup}}';
	}

	public function behaviors()
	{
		return [
			[
				'class' => \shopack\base\common\behaviors\RowDatesAttributesBehavior::class,
				'createdAtAttribute' => 'usragpCreatedAt',
				'createdByAttribute' => 'usragpCreatedBy',
				'updatedAtAttribute' => 'usragpUpdatedAt',
				'updatedByAttribute' => 'usragpUpdatedBy',
			],
		];
	}

}
