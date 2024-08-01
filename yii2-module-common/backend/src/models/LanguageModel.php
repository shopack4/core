<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\cmn\backend\models;

use shopack\cmn\backend\classes\CommonActiveRecord;

class LanguageModel extends CommonActiveRecord
{
  use \shopack\cmn\common\models\LanguageModelTrait;

	public static function tableName()
	{
		return '{{%CMN_Language}}';
	}

	public function behaviors()
	{
		return [
			[
				'class' => \shopack\base\common\behaviors\RowDatesAttributesBehavior::class,
				'createdAtAttribute' => 'lngCreatedAt',
				'createdByAttribute' => 'lngCreatedBy',
				'updatedAtAttribute' => 'lngUpdatedAt',
				'updatedByAttribute' => 'lngUpdatedBy',
			],
		];
	}

}
