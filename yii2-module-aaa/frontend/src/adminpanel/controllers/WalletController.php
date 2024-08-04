<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\aaa\frontend\adminpanel\controllers;

use shopack\aaa\frontend\common\auth\BaseCrudController;
use shopack\aaa\frontend\common\models\WalletModel;
use shopack\aaa\frontend\common\models\WalletSearchModel;

class WalletController extends BaseCrudController
{
	public $modelClass = WalletModel::class;
	public $searchModelClass = WalletSearchModel::class;

}
