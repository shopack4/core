<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\aaa\backend\models;

use Yii;
use yii\base\Model;
use shopack\aaa\backend\models\VoucherModel;
use shopack\aaa\common\enums\enuVoucherType;
use shopack\aaa\common\enums\enuVoucherStatus;

class BasketForm extends Model
{
	public static function getCurrentBasket($userid = null)
	{
    return VoucherModel::find()
      ->andWhere(['vchOwnerUserID' => $userid ?? Yii::$app->user->id])
      ->andWhere(['vchType' => enuVoucherType::Basket])
      ->andWhere(['vchStatus' => enuVoucherStatus::New])
      ->andWhere(['vchRemovedAt' => 0])
      ->one();
	}

}
