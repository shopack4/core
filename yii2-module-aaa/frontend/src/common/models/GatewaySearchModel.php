<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\aaa\frontend\common\models;

use yii\base\Model;
use yii\web\ServerErrorHttpException;
use shopack\base\frontend\common\rest\RestClientDataProvider;
use shopack\aaa\frontend\common\models\GatewayModel;

class GatewaySearchModel extends GatewayModel
{
	use \shopack\base\common\db\SearchModelTrait;

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
					'gtwID',
					'gtwName',
					'gtwUUID',
					'gtwPluginType',
					'gtwPluginName',
					// 'gtwPluginParameters',
					'gtwStatus',
					'gtwCreatedAt' => [
						'default' => SORT_DESC,
					],
					'gtwCreatedBy',
					'gtwUpdatedAt' => [
						'default' => SORT_DESC,
					],
					'gtwUpdatedBy',
					'gtwRemovedAt' => [
						'default' => SORT_DESC,
					],
					'gtwRemovedBy',
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

		$this->applySearchValuesInQuery($query, $params);

		return $dataProvider;
	}

}
