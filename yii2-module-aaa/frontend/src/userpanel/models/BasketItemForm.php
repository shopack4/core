<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\aaa\frontend\userpanel\models;

use Yii;
use yii\base\Model;
use shopack\base\common\helpers\Url;
use shopack\base\common\helpers\HttpHelper;
use shopack\base\frontend\rest\RestClientActiveRecord;
use shopack\aaa\common\enums\enuVoucherStatus;
use shopack\base\common\rest\enuColumnInfo;

class BasketItemForm extends RestClientActiveRecord
{
	// use \shopack\aaa\common\models\BasketItemTrait;

	public static $resourceName = 'aaa/basket/item';
  public static $primaryKey = ['itemkey'];

	public static function columnsInfo()
	{
		return [
			'itemkey' => [
				enuColumnInfo::type       => 'string',
        enuColumnInfo::validator  => null,
        enuColumnInfo::default    => null,
        enuColumnInfo::required   => true,
        enuColumnInfo::selectable => true,
			],
		];
	}

	public function primaryKeyValue()
	{
		return $this->itemkey;
	}

	public function isSoftDeleted()
	{
		return false;
	}

}
