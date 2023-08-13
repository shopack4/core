<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\aaa\frontend\adminpanel\controllers;

use Yii;
use yii\web\Response;
use shopack\aaa\frontend\common\auth\BaseCrudController;
use shopack\base\common\helpers\HttpHelper;
use shopack\aaa\frontend\common\models\GeoCountryModel;
use shopack\aaa\frontend\common\models\GeoCountrySearchModel;

class GeoCountryController extends BaseCrudController
{
	public $modelClass = GeoCountryModel::class;
	public $searchModelClass = GeoCountrySearchModel::class;

}
