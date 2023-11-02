<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\aaa\frontend\common\models;

use yii\base\Model;
use yii\web\ServerErrorHttpException;
use shopack\base\frontend\common\rest\RestClientDataProvider;
use shopack\aaa\frontend\common\models\AccessGroupModel;

class AccessGroupSearchModel extends AccessGroupModel
{
	use \shopack\base\common\db\SearchModelTrait;

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
					'usragpID',
					'usragpCreatedAt' => [
						'default' => SORT_DESC,
					],
					'usragpCreatedBy',
					'usragpUpdatedAt' => [
						'default' => SORT_DESC,
					],
					'usragpUpdatedBy',
					'usragpRemovedAt' => [
						'default' => SORT_DESC,
					],
					'usragpRemovedBy',
				],
			],
		]);

		$this->load($params);

		if (!$this->validate()) {
			// uncomment the following line if you do not want to return any records when validation fails
			// $query->where('0=1');
			return $dataProvider;
		}

		$this->applySearchValuesInQuery($query, $params);

		return $dataProvider;
	}

}
