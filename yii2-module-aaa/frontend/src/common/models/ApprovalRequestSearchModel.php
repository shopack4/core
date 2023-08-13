<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\aaa\frontend\common\models;

use yii\base\Model;
use shopack\base\frontend\rest\RestClientDataProvider;
use shopack\aaa\frontend\common\models\ApprovalRequestModel;

class ApprovalRequestSearchModel extends ApprovalRequestModel
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
					'aprID',
					'aprName',
					'aprKey',
					'aprStatus',
					'aprCreatedAt' => [
						'default' => SORT_DESC,
					],
					'aprCreatedBy',
					'aprUpdatedAt' => [
						'default' => SORT_DESC,
					],
					'aprUpdatedBy',
					'aprRemovedAt' => [
						'default' => SORT_DESC,
					],
					'aprRemovedBy',
				],
			],
		]);

		$this->load($params);

		if (!$this->validate()) {
			// uncomment the following line if you do not want to return any records when validation fails
			// $query->where('0=1');
			return $dataProvider;
		}

		$this->applySearchValuesInQuery($query);

		return $dataProvider;
	}

}
