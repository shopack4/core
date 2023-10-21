<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\aaa\frontend\adminpanel\controllers;

use shopack\base\common\helpers\Url;
use shopack\base\common\helpers\StringHelper;
use shopack\aaa\frontend\common\auth\BaseCrudController;
use shopack\aaa\frontend\common\models\DeliveryMethodModel;
use shopack\aaa\frontend\common\models\DeliveryMethodSearchModel;

class DeliveryMethodController extends BaseCrudController
{
	public $modelClass = DeliveryMethodModel::class;
	public $searchModelClass = DeliveryMethodSearchModel::class;

}
