<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\aaa\backend\models;

use shopack\aaa\backend\classes\AAAActiveRecord;

class AccessGroupModel extends AAAActiveRecord
{
  use \shopack\aaa\common\models\AccessGroupModelTrait;

	public static function tableName()
	{
		return '{{%AAA_AccessGroup}}';
	}

	public function behaviors()
	{
		return [
			[
				'class' => \shopack\base\common\behaviors\RowDatesAttributesBehavior::class,
				'createdAtAttribute' => 'agpCreatedAt',
				'createdByAttribute' => 'agpCreatedBy',
				'updatedAtAttribute' => 'agpUpdatedAt',
				'updatedByAttribute' => 'agpUpdatedBy',
			],
		];
	}

}
