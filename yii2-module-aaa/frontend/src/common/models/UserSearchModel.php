<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\aaa\frontend\common\models;

// use Yii;
use yii\base\Model;
use yii\web\ServerErrorHttpException;
use shopack\base\frontend\rest\RestClientDataProvider;
// use shopack\base\helpers\Html;
// use shopack\base\helpers\ArrayHelper;
use shopack\aaa\frontend\common\models\UserModel;

class UserSearchModel extends UserModel
{
	use \shopack\base\common\db\SearchModelTrait;

	public $sessionCount;
	public $lastActivity;

	// public function attributeLabels()
	// {
	// 	return ArrayHelper::merge(parent::attributeLabels(), [
	// 		'usrssnLoginDateTime' => 'آخرین ورود',
	// 		'loginDateTime' => 'آخرین ورود',
	// 		'online' => 'آنلاین',
	// 	]);
	// }

	public function scenarios()
	{
		// bypass scenarios() implementation in the parent class
		return Model::scenarios();
	}

	public function search($params)
	{
		$query = self::find();

		$dataProvider = new RestClientDataProvider([
			'query' => $query,
			'sort' => [
				// 'enableMultiSort' => true,
				'attributes' => [
					'usrID',
					'usrFirstName' => [
						'asc' => ['usrFirstName' => SORT_ASC, 'usrLastName' => SORT_ASC],
						'desc' => ['usrFirstName' => SORT_DESC, 'usrLastName' => SORT_DESC],
						//'label' => 'usrssnLoginDateTime',
						'default' => SORT_ASC
					],
					'usrLastName' => [
						'asc' => ['usrLastName' => SORT_ASC, 'usrFirstName' => SORT_ASC],
						'desc' => ['usrLastName' => SORT_DESC, 'usrFirstName' => SORT_DESC],
						//'label' => 'usrssnLoginDateTime',
						'default' => SORT_ASC
					],
					'usrEmail',
					'usrMobile',
					'usrSSID',
					// 'usrssnLoginDateTime' => [
						// 'asc' => ['usrssn.usrssnLoginDateTime' => SORT_ASC],
						// 'desc' => ['usrssn.usrssnLoginDateTime' => SORT_DESC],
						// 'default' => SORT_DESC
					// ],
					'usrCreatedAt' => [
						'default' => SORT_DESC
					],
					'usrUpdatedAt' => [
						'default' => SORT_DESC
					],
					// 'online' => [
					// 	'asc' => ['sessionCount' => SORT_ASC],
					// 	'desc' => ['sessionCount' => SORT_DESC],
					// 	'default' => SORT_DESC,
					// ],
					// 'usrLastLoginAt' => [
					// 	'default' => SORT_DESC,
					// ],
					// 'usrLastActivityAt' => [
					// 	'default' => SORT_DESC,
					// ],
					'rolName',
				],
				'defaultOrder' => [
					'usrCreatedAt' => SORT_DESC,
					// 'usrLastName' => SORT_ASC,
					// 'usrFirstName' => SORT_ASC,
				],
			],
		]);

		$this->load($params);

		if (!$this->validate()) {
			// uncomment the following line if you do not want to return any records when validation fails
			throw new ServerErrorHttpException('Unknown error sh01');
			// $query->where('0=1');
			return $dataProvider;
		}

		$this->applySearchValuesInQuery($query);

		return $dataProvider;
	}

}
