<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\aaa\frontend\common\models;

use yii\base\Model;
use shopack\base\frontend\common\rest\RestClientDataProvider;
use shopack\aaa\frontend\common\models\SessionModel;

class SessionSearchModel extends SessionModel
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
					'ssnID',
					// 'ssnStatus',
					'ssnCreatedAt' => [
						'default' => SORT_DESC,
					],
					'ssnCreatedBy',
					'ssnUpdatedAt' => [
						'default' => SORT_DESC,
					],
					'ssnUpdatedBy',
					'ssnRemovedAt' => [
						'default' => SORT_DESC,
					],
					'ssnRemovedBy',
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
