<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\aaa\frontend\adminpanel\controllers;

use shopack\aaa\frontend\common\auth\BaseCrudController;
use shopack\aaa\frontend\common\models\OnlinePaymentModel;
use shopack\aaa\frontend\common\models\OnlinePaymentSearchModel;

class OnlinePaymentController extends BaseCrudController
{
	public $modelClass = OnlinePaymentModel::class;
	public $searchModelClass = OnlinePaymentSearchModel::class;

  public function behaviors()
	{
		$behaviors = parent::behaviors();

		$behaviors[static::BEHAVIOR_AUTHENTICATOR]['except'] = [
			'webhook',
    ];

		return $behaviors;
	}

}
