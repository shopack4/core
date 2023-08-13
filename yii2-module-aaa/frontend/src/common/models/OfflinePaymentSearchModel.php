<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\aaa\frontend\common\models;

use yii\base\Model;
use shopack\base\common\helpers\ArrayHelper;
use shopack\base\frontend\rest\RestClientDataProvider;
use shopack\aaa\frontend\common\models\OfflinePaymentModel;

class OfflinePaymentSearchModel extends OfflinePaymentModel
{
	use \shopack\base\common\db\SearchModelTrait;

	// public function extraRules()
	// {
	// 	return [
	// 		[[
	// 			'ofpOwnerUserID',
	// 		], 'number'],
	// 		[[
	// 			'ofpOwnerUserID',
	// 		], 'default', 'value' => null],
	// 	];
	// }

	// public function attributeLabels()
	// {
	// 	return ArrayHelper::merge(parent::attributeLabels(), [
	// 		'ofpOwnerUserID' => 'مالک',
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
					'ofpID',
					'ofpUUID',
					'ofpAmount',
					'ofpStatus',
					'ofpCreatedAt' => [
						'asc'		=> ['ofpCreatedAt' => SORT_ASC,	 'ofpID' => SORT_ASC],
						'desc'	=> ['ofpCreatedAt' => SORT_DESC, 'ofpID' => SORT_DESC],
						'default' => SORT_DESC,
					],
					'ofpCreatedBy',
					'ofpUpdatedAt' => [
						'default' => SORT_DESC,
					],
					'ofpUpdatedBy',
					'ofpRemovedAt' => [
						'default' => SORT_DESC,
					],
					'ofpRemovedBy',
					'ofpPayDate' => [
						'default' => SORT_DESC,
					],
				],
				'defaultOrder' => [
					'ofpPayDate' => SORT_DESC,
				],
			],
		]);

		$this->load($params);

		if (!$this->validate()) {
			// uncomment the following line if you do not want to return any records when validation fails
			// $query->where('0=1');
			return $dataProvider;
		}

		if (empty($this->ofpOwnerUserID) == false)
			$query->andWhere(['ofpOwnerUserID' => $this->ofpOwnerUserID]);
		else if (empty($params['ofpOwnerUserID']) == false)
			$query->andWhere(['ofpOwnerUserID' => $params['ofpOwnerUserID']]);

		$this->applySearchValuesInQuery($query);

		return $dataProvider;
	}

}
