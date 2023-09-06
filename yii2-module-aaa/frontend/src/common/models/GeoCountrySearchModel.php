<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\aaa\frontend\common\models;

use yii\base\Model;
use yii\web\ServerErrorHttpException;
use shopack\base\frontend\rest\RestClientDataProvider;
use shopack\aaa\frontend\common\models\GeoCountryModel;

class GeoCountrySearchModel extends GeoCountryModel
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
					'cntrID',
					'cntrName',
					// 'cntrStatus',
					'cntrCreatedAt' => [
						'default' => SORT_DESC,
					],
					'cntrCreatedBy',
					'cntrUpdatedAt' => [
						'default' => SORT_DESC,
					],
					'cntrUpdatedBy',
					'cntrRemovedAt' => [
						'default' => SORT_DESC,
					],
					'cntrRemovedBy',
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
