<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\aaa\backend\models;

use shopack\aaa\backend\classes\AAAActiveRecord;

class DeliveryMethodModel extends AAAActiveRecord
{
  use \shopack\aaa\common\models\DeliveryMethodModelTrait;

	public static function tableName()
	{
		return '{{%AAA_DeliveryMethod}}';
	}

	public function behaviors()
	{
		return [
			[
				'class' => \shopack\base\common\behaviors\RowDatesAttributesBehavior::class,
				'createdAtAttribute' => 'dlvCreatedAt',
				'createdByAttribute' => 'dlvCreatedBy',
				'updatedAtAttribute' => 'dlvUpdatedAt',
				'updatedByAttribute' => 'dlvUpdatedBy',
			],
		];
	}

}
