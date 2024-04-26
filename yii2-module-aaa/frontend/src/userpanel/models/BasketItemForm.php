<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\aaa\frontend\userpanel\models;

use InvalidArgumentException;
use Yii;
use yii\base\Model;
use shopack\base\common\helpers\Url;
use shopack\base\common\helpers\HttpHelper;
use shopack\base\frontend\common\rest\RestClientActiveRecord;
use shopack\aaa\common\enums\enuVoucherStatus;
use shopack\base\common\rest\enuColumnInfo;
use shopack\base\common\rest\enuColumnSearchType;
use yii\web\NotFoundHttpException;

class BasketItemForm extends Model //RestClientActiveRecord
{
	// use \shopack\aaa\common\models\BasketItemTrait;

	// public static $resourceName = 'aaa/basket/item';
  // public static $primaryKey = ['itemkey'];

	// public function columnsInfo()
	// {
	// 	return [
	// 		'itemkey' => [
	// 			enuColumnInfo::type       => 'string',
  //       enuColumnInfo::validator  => null,
  //       enuColumnInfo::default    => null,
  //       enuColumnInfo::required   => true,
  //       enuColumnInfo::selectable => true,
	// 		],
	// 	];
	// }

	// public function primaryKeyValue()
	// {
	// 	return $this->itemkey;
	// }

	// public function isSoftDeleted()
	// {
	// 	return false;
	// }

	public function removeItem($key)
	{
		$parts = explode('/', $key, 2);

		if (count($parts) != 2) {
			throw new NotFoundHttpException('Invalid Key');
		}

		$service = $parts[0];
		$key = $parts[1];

		list ($resultStatus, $resultData) = HttpHelper::callApi(
			$service . '/accounting/remove-basket-item',
      HttpHelper::METHOD_POST,
      [],
      [
				'itemKey' => $key,
      ]
    );

    if ($resultStatus < 200 || $resultStatus >= 300)
      throw new \yii\web\HttpException($resultStatus, Yii::t('aaa', $resultData['message'], $resultData));

    return true; //[$resultStatus, $resultData['result']];
	}

}
