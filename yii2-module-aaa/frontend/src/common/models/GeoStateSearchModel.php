<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\aaa\frontend\common\models;

use yii\base\Model;
use shopack\base\frontend\rest\RestClientDataProvider;
use shopack\aaa\frontend\common\models\GeoStateModel;

class GeoStateSearchModel extends GeoStateModel
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
					'sttID',
					'sttName',
					'sttCountryID',
					// 'sttStatus',
					'sttCreatedAt' => [
						'default' => SORT_DESC,
					],
					'sttCreatedBy',
					'sttUpdatedAt' => [
						'default' => SORT_DESC,
					],
					'sttUpdatedBy',
					'sttRemovedAt' => [
						'default' => SORT_DESC,
					],
					'sttRemovedBy',
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
