<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\aaa\backend\models;

use Yii;
use shopack\aaa\backend\classes\AAAActiveRecord;
use shopack\aaa\common\enums\enuBasicDefinitionStatus;

class BasicDefinitionModel extends AAAActiveRecord
{
	use \shopack\aaa\common\models\BasicDefinitionModelTrait;

  use \shopack\base\common\db\SoftDeleteActiveRecordTrait;
  public function initSoftDelete()
  {
    $this->softdelete_RemovedStatus  = enuBasicDefinitionStatus::Removed;
    // $this->softdelete_StatusField    = 'bdfType';
    $this->softdelete_RemovedAtField = 'bdfRemovedAt';
    $this->softdelete_RemovedByField = 'bdfRemovedBy';
	}

	public static function tableName()
	{
		return '{{%AAA_BasicDefinition}}';
	}

	public function behaviors()
	{
		return [
			[
				'class' => \shopack\base\common\behaviors\RowDatesAttributesBehavior::class,
				'createdAtAttribute' => 'bdfCreatedAt',
				'createdByAttribute' => 'bdfCreatedBy',
				'updatedAtAttribute' => 'bdfUpdatedAt',
				'updatedByAttribute' => 'bdfUpdatedBy',
			],
		];
	}

}
